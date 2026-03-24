<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Contracts\PasswordResetMailer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    public function __construct(protected PasswordResetMailer $mailer) {}

    // ═══════════════════════════════════════════════════════════
    // Activity Log — ZERO changes
    // ═══════════════════════════════════════════════════════════

    private function activityActor(Request $r): array
    {
        return [
            'role' => $r->attributes->get('auth_role'),
            'id'   => (int) ($r->attributes->get('auth_tokenable_id') ?? 0),
        ];
    }

    private function logActivity(
        Request $request,
        string $activity,
        string $note,
        string $tableName,
        ?int $recordId = null,
        ?array $changedFields = null,
        ?array $oldValues = null,
        ?array $newValues = null
    ): void {
        $a = $this->activityActor($request);

        try {
            DB::table('user_data_activity_log')->insert([
                'performed_by'      => $a['id'] ?: 0,
                'performed_by_role' => $a['role'] ?: null,
                'ip'                => $request->ip(),
                'user_agent'        => (string) $request->userAgent(),
                'activity'          => $activity,
                'module'            => 'ForgotPassword',
                'table_name'        => $tableName,
                'record_id'         => $recordId,
                'changed_fields'    => $changedFields ? json_encode(array_values($changedFields), JSON_UNESCAPED_UNICODE) : null,
                'old_values'        => $oldValues  ? json_encode($oldValues,  JSON_UNESCAPED_UNICODE) : null,
                'new_values'        => $newValues  ? json_encode($newValues,  JSON_UNESCAPED_UNICODE) : null,
                'log_note'          => $note,
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('[ForgotPassword] user_data_activity_log insert failed', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /* =========================================================
     | API 1 — POST /api/auth/forgot-password/send-otp
     | body: { identifier }  ← email OR phone number
     * ========================================================= */

    public function sendOtp(Request $r)
    {
        $reqId = (string) Str::uuid();

        Log::channel('daily')->info('FP_SEND_OTP:HIT', [
            'request_id' => $reqId,
            'method'     => $r->method(),
            'path'       => $r->path(),
            'full_url'   => $r->fullUrl(),
            'ip'         => $r->ip(),
            'ua'         => substr((string) $r->userAgent(), 0, 180),
            'ts'         => now()->toDateTimeString(),
        ]);

        $r->validate([
            'identifier' => ['required', 'string', 'max:255'],
        ]);

        $identifier = trim($r->identifier);
        $isEmail    = filter_var($identifier, FILTER_VALIDATE_EMAIL) !== false;
        $identifier = $isEmail ? strtolower($identifier) : preg_replace('/\s+/', '', $identifier);

        Log::channel('daily')->info('FP_SEND_OTP:AFTER_VALIDATE', [
            'request_id' => $reqId,
            'identifier' => $identifier,
            'type'       => $isEmail ? 'email' : 'phone',
        ]);

        // ── Rate-limit check — by IP ──────────────────────────────────
        $ip = $r->ip();

        $ipRecord = DB::table('password_reset_tokens')
            ->where('system_ip', $ip)
            ->whereDate('created_at', today())
            ->orderBy('created_at', 'desc')
            ->first();

        if ($ipRecord) {

            // 3rd attempt done — blocked until next day
            if ($ipRecord->attempt_count >= 3) {

                Log::channel('daily')->warning('FP_SEND_OTP:HARD_LOCKED', [
                    'request_id' => $reqId,
                    'identifier' => $identifier,
                    'ip'         => $ip,
                ]);

                $this->logActivity(
                    $r,
                    'store',
                    'OTP request blocked — IP hard-locked until tomorrow',
                    'password_reset_tokens',
                    null,
                    ['system_ip'],
                    null,
                    ['ip' => $ip, 'identifier' => $identifier, 'request_id' => $reqId]
                );

                return response()->json([
                    'status'  => 'error',
                    'message' => 'Too many attempts. Please try again tomorrow.',
                    'locked'  => true,
                ], 429);
            }

            // After 1st attempt — 2 min cooldown
            if ($ipRecord->attempt_count === 1) {
                $cooldown = Carbon::parse($ipRecord->updated_at)->addMinutes(2);
                if (now()->lessThan($cooldown)) {

                    Log::channel('daily')->warning('FP_SEND_OTP:COOLDOWN_ACTIVE', [
                        'request_id'  => $reqId,
                        'identifier'  => $identifier,
                        'ip'          => $ip,
                        'wait_seconds'=> now()->diffInSeconds($cooldown),
                    ]);

                    return response()->json([
                        'status'       => 'error',
                        'message'      => 'Please wait before requesting another OTP.',
                        'wait_seconds' => now()->diffInSeconds($cooldown),
                        'locked'       => false,
                    ], 429);
                }
            }

            // After 2nd attempt — 5 min cooldown
            if ($ipRecord->attempt_count === 2) {
                $cooldown = Carbon::parse($ipRecord->updated_at)->addMinutes(5);
                if (now()->lessThan($cooldown)) {

                    Log::channel('daily')->warning('FP_SEND_OTP:COOLDOWN_ACTIVE', [
                        'request_id'  => $reqId,
                        'identifier'  => $identifier,
                        'ip'          => $ip,
                        'wait_seconds'=> now()->diffInSeconds($cooldown),
                    ]);

                    return response()->json([
                        'status'       => 'error',
                        'message'      => 'Please wait before requesting another OTP.',
                        'wait_seconds' => now()->diffInSeconds($cooldown),
                        'locked'       => false,
                    ], 429);
                }
            }
        }

        $attempts     = $ipRecord ? $ipRecord->attempt_count + 1 : 1;
        $nextCooldown = match($attempts) {
            1       => 120,   // 2 min
            2       => 300,   // 5 min
            default => null,
        };

        Log::channel('daily')->info('FP_SEND_OTP:RATE_LIMIT_UPDATED', [
            'request_id'    => $reqId,
            'identifier'    => $identifier,
            'ip'            => $ip,
            'attempt'       => $attempts,
            'next_cooldown' => $nextCooldown,
        ]);

        // ── Everything below is ZERO changes ─────────────────────────
        $genericMessage = 'If this account exists in our system, an OTP has been sent to your registered contact.';

        $userRow = $isEmail
            ? DB::table('users')->select('id', 'email', 'phone_number')->where('email', $identifier)->first()
            : DB::table('users')->select('id', 'email', 'phone_number')->where('phone_number', $identifier)->first();

        Log::channel('daily')->info('FP_SEND_OTP:USER_EXISTS_CHECK', [
            'request_id'  => $reqId,
            'identifier'  => $identifier,
            'user_exists' => (bool) $userRow,
        ]);

        if (!$userRow) {
            $this->logActivity(
                $r,
                'store',
                'OTP requested — user not found (silent success)',
                'password_reset_tokens',
                null,
                ['identifier'],
                null,
                ['identifier' => $identifier, 'request_id' => $reqId]
            );

            Log::channel('daily')->warning('FP_SEND_OTP:USER_NOT_FOUND_SILENT_SUCCESS', [
                'request_id' => $reqId,
                'identifier' => $identifier,
            ]);

            return response()->json([
                'status'           => 'success',
                'message'          => $genericMessage,
                'cooldown_seconds' => $nextCooldown,
                'is_final_attempt' => $attempts === 3,
                'data'             => [
                    'request_id' => $reqId,
                    'email'      => $isEmail ? $identifier : null,
                    'phone'      => $isEmail ? null : $identifier,
                ],
            ]);
        }

        $email    = !empty($userRow->email)        ? strtolower(trim($userRow->email))  : null;
        $phone    = !empty($userRow->phone_number) ? trim($userRow->phone_number)        : null;
        $tokenKey = $email ?? $phone;

        Log::channel('daily')->info('FP_SEND_OTP:RESOLVED', [
            'request_id' => $reqId,
            'has_email'  => (bool) $email,
            'has_phone'  => (bool) $phone,
            'token_key'  => $tokenKey,
        ]);

        $invalidated = DB::table('password_reset_tokens')
            ->where('email', $tokenKey)
            ->whereNull('verified_at')
            ->update(['verified_at' => Carbon::now()]);

        Log::channel('daily')->info('FP_SEND_OTP:INVALIDATED_OLD_TOKENS', [
            'request_id'        => $reqId,
            'token_key'         => $tokenKey,
            'invalidated_count' => (int) $invalidated,
        ]);

        $otp       = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $now       = Carbon::now();
        $expiresAt = $now->copy()->addMinutes(10);

        Log::channel('daily')->info('FP_SEND_OTP:OTP_GENERATED', [
            'request_id' => $reqId,
            'token_key'  => $tokenKey,
            'expires_at' => $expiresAt->toDateTimeString(),
        ]);

        try {
            DB::table('password_reset_tokens')->where('email', $tokenKey)->delete();

            // Keep first click IP — never overwrite
            $existingIpRecord = DB::table('password_reset_tokens')
                ->where('system_ip', $ip)
                ->whereDate('created_at', today())
                ->first();

            DB::table('password_reset_tokens')->insert([
                'email'         => $tokenKey,
                'token'         => Str::random(64),
                'phone_no'      => $phone,
                'otp'           => $otp,
                'expires_at'    => $expiresAt,
                'verified_at'   => null,
                'system_ip'     => $existingIpRecord->system_ip ?? $ip, // ← first IP only
                'attempt_count' => $attempts,
                'created_at'    => $now,
                'updated_at'    => $now,
            ]);

            Log::channel('daily')->info('FP_SEND_OTP:INSERT_OK', [
                'request_id' => $reqId,
                'token_key'  => $tokenKey,
            ]);

            $this->logActivity(
                $r,
                'store',
                'OTP generated and stored — valid 10 minutes',
                'password_reset_tokens',
                null,
                ['email', 'phone_no', 'otp', 'expires_at', 'system_ip', 'attempt_count'],
                null,
                [
                    'token_key'     => $tokenKey,
                    'phone_no'      => $phone,
                    'expires_at'    => $expiresAt->toDateTimeString(),
                    'system_ip'     => $ip,
                    'attempt_count' => $attempts,
                    'request_id'    => $reqId,
                ]
            );

        } catch (\Throwable $e) {
            Log::channel('daily')->error('FP_SEND_OTP:INSERT_FAILED', [
                'request_id' => $reqId,
                'token_key'  => $tokenKey,
                'error'      => $e->getMessage(),
            ]);

            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to generate OTP. Please try again.',
                'data'    => ['request_id' => $reqId],
            ], 500);
        }

        if ($email) {
            $this->mailer->sendOtp($email, $otp, $phone);
        } else {
            $this->mailer->sendOtp(null, $otp, $phone);
        }

        Log::channel('daily')->info('FP_SEND_OTP:DISPATCHED', [
            'request_id' => $reqId,
            'has_email'  => (bool) $email,
            'has_phone'  => (bool) $phone,
        ]);

        return response()->json([
            'status'           => 'success',
            'message'          => $genericMessage,
            'cooldown_seconds' => $nextCooldown,       // ← frontend timer
            'is_final_attempt' => $attempts === 3,     // ← frontend disables button
            'data'             => [
                'request_id'         => $reqId,
                'expires_in_minutes' => 10,
                'email'              => $email,
                'phone'              => $phone,
                'token_key'          => $tokenKey,
            ],
        ]);
    }

    /* =========================================================
     | API 2 — POST /api/auth/forgot-password/reset
     | Removed: clearRateLimitCache() — no longer needed
     * ========================================================= */

    public function resetPassword(Request $r)
    {
        $reqId = (string) Str::uuid();

        Log::channel('daily')->info('FP_RESET:HIT', [
            'request_id' => $reqId,
            'method'     => $r->method(),
            'path'       => $r->path(),
            'ip'         => $r->ip(),
            'ts'         => now()->toDateTimeString(),
        ]);

        $r->validate([
            'token_key' => ['required', 'string', 'max:255'],
            'otp'       => ['required', 'string', 'digits:6'],
            'password'  => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $tokenKey = trim($r->token_key);
        $otp      = $r->otp;

        $row = DB::table('password_reset_tokens')
            ->where('email', $tokenKey)
            ->whereNull('verified_at')
            ->first();

        Log::channel('daily')->info('FP_RESET:RECORD_FETCH', [
            'request_id'   => $reqId,
            'token_key'    => $tokenKey,
            'record_found' => (bool) $row,
        ]);

        if (!$row) {
            return response()->json([
                'status'  => 'error',
                'message' => 'This OTP is invalid or has expired.',
            ], 422);
        }

        if (Carbon::parse($row->expires_at)->isPast()) {
            DB::table('password_reset_tokens')
                ->where('email', $tokenKey)
                ->update(['verified_at' => Carbon::now()]);

            $this->logActivity(
                $r,
                'update',
                'OTP expired (10 min window passed) — invalidated',
                'password_reset_tokens',
                null,
                ['verified_at'],
                ['verified_at' => null,             'token_key' => $tokenKey],
                ['verified_at' => Carbon::now()->toDateTimeString(), 'token_key' => $tokenKey]
            );

            Log::channel('daily')->warning('FP_RESET:OTP_EXPIRED', [
                'request_id' => $reqId,
                'token_key'  => $tokenKey,
                'expired_at' => $row->expires_at,
            ]);

            return response()->json([
                'status'  => 'error',
                'message' => 'This OTP is invalid or has expired.',
            ], 422);
        }

        if ($row->otp !== $otp) {
            Log::channel('daily')->warning('FP_RESET:OTP_MISMATCH', [
                'request_id' => $reqId,
                'token_key'  => $tokenKey,
            ]);

            return response()->json([
                'status'  => 'error',
                'message' => 'This OTP is invalid or has expired.',
            ], 422);
        }

        $isEmail = filter_var($tokenKey, FILTER_VALIDATE_EMAIL) !== false;

        $userRow = $isEmail
            ? DB::table('users')->select('id', 'email', 'phone_number')->where('email', $tokenKey)->first()
            : DB::table('users')->select('id', 'email', 'phone_number')->where('phone_number', $tokenKey)->first();

        if (!$userRow) {
            DB::table('password_reset_tokens')
                ->where('email', $tokenKey)
                ->update(['verified_at' => Carbon::now()]);

            $this->logActivity(
                $r,
                'update',
                'User not found during reset — invalidated OTP record',
                'password_reset_tokens',
                null,
                ['verified_at'],
                ['verified_at' => null,             'token_key' => $tokenKey],
                ['verified_at' => Carbon::now()->toDateTimeString(), 'token_key' => $tokenKey]
            );

            Log::channel('daily')->error('FP_RESET:USER_NOT_FOUND', [
                'request_id' => $reqId,
                'token_key'  => $tokenKey,
            ]);

            return response()->json([
                'status'  => 'error',
                'message' => 'User not found.',
            ], 404);
        }

        $updateQuery = DB::table('users');
        if ($isEmail) {
            $updateQuery->where('email', $tokenKey);
        } else {
            $updateQuery->where('phone_number', $tokenKey);
        }

        $updateQuery->update([
            'password'   => Hash::make($r->password),
            'updated_at' => Carbon::now(),
        ]);

        Log::channel('daily')->info('FP_RESET:PASSWORD_UPDATED', [
            'request_id' => $reqId,
            'token_key'  => $tokenKey,
            'user_id'    => $userRow->id,
        ]);

        $this->logActivity(
            $r,
            'update',
            'Password reset successful — user password updated',
            'users',
            (int) $userRow->id,
            ['password', 'updated_at'],
            ['token_key' => $tokenKey],
            ['token_key' => $tokenKey]
        );

        DB::table('password_reset_tokens')
            ->where('email', $tokenKey)
            ->update(['verified_at' => Carbon::now()]);

        $this->logActivity(
            $r,
            'update',
            'OTP marked verified and consumed after successful reset',
            'password_reset_tokens',
            null,
            ['verified_at'],
            ['verified_at' => null,                              'token_key' => $tokenKey],
            ['verified_at' => Carbon::now()->toDateTimeString(), 'token_key' => $tokenKey]
        );

        Log::channel('daily')->info('FP_RESET:OTP_CONSUMED', [
            'request_id' => $reqId,
            'token_key'  => $tokenKey,
        ]);

        // ── No cache to clear — DB resets naturally at midnight ───────

        return response()->json([
            'status'  => 'success',
            'message' => 'Your password has been successfully updated.',
        ]);
    }
}