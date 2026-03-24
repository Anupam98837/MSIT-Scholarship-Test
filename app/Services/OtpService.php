<?php

namespace App\Services;

use App\Models\OtpVerification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OtpService
{
public function sendOtp(string $phone, string $ip = null): array
{
    // ── Find today's record by this IP ──────────────────────────────
    $ipRecord = DB::table('otp_verifications')
        ->where('system_ip', $ip)
        ->whereDate('created_at', today())
        ->orderBy('created_at', 'desc')
        ->first();

    if ($ipRecord) {

        // 3rd attempt already done — blocked until next day
        if ($ipRecord->attempt_count >= 3) {
            return [
                'success' => false,
                'message' => 'Too many attempts. Please try again tomorrow.',
            ];
        }

        // After 1st attempt — enforce 2 min cooldown
        if ($ipRecord->attempt_count == 1) {
            $cooldown = \Carbon\Carbon::parse($ipRecord->updated_at)->addMinutes(2);
            if (now()->lessThan($cooldown)) {
                return [
                    'success'      => false,
                    'message'      => 'Please wait before retrying.',
                    'wait_seconds' => now()->diffInSeconds($cooldown),
                ];
            }
        }

        // After 2nd attempt — enforce 5 min cooldown
        if ($ipRecord->attempt_count == 2) {
            $cooldown = \Carbon\Carbon::parse($ipRecord->updated_at)->addMinutes(5);
            if (now()->lessThan($cooldown)) {
                return [
                    'success'      => false,
                    'message'      => 'Please wait before retrying.',
                    'wait_seconds' => now()->diffInSeconds($cooldown),
                ];
            }
        }
    }

    // ── Generate OTP ────────────────────────────────────────────────
    $otp          = rand(100000, 999999);
    $attemptCount = $ipRecord ? $ipRecord->attempt_count + 1 : 1;

    // ── Insert or Update ────────────────────────────────────────────
    $existing = DB::table('otp_verifications')
        ->where('phone_number', $phone)
        ->first();

    if ($existing) {
        DB::table('otp_verifications')
            ->where('phone_number', $phone)
            ->update([
                'otp'           => $otp,
                'expires_at'    => now()->addMinutes(10),
                'is_used'       => 0,
                'system_ip'     => $existing->system_ip ?? $ip, // ← keep first IP
                'attempt_count' => $attemptCount,
                'updated_at'    => now(),
            ]);
    } else {
        DB::table('otp_verifications')->insert([
            'phone_number'  => $phone,
            'system_ip'     => $ip,                             // ← store on first click
            'otp'           => $otp,
            'expires_at'    => now()->addMinutes(10),
            'is_used'       => 0,
            'attempt_count' => 1,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);
    }

    // Send OTP via SMS here...
        $sent = $this->callVoicenSmsApi($phone, (string) $otp);


    // ── Cooldown info for frontend ──────────────────────────────────
    $cooldownSeconds = match($attemptCount) {
        1 => 2 * 60,   // 2 min
        2 => 5 * 60,   // 5 min
        3 => null,     // blocked
        default => null
    };

    return [
        'success'          => true,
        'message'          => 'OTP sent successfully.',
        'attempt_count'    => $attemptCount,
        'cooldown_seconds' => $cooldownSeconds,
        'is_final_attempt' => $attemptCount === 3,
    ];
}
    public function verifyOtp(string $phone, string $otp): array
    {
        $record = OtpVerification::where('phone_number', $phone)
            ->where('is_used', false)
            ->latest()
            ->first();

        if (!$record) {
            return ['success' => false, 'message' => 'OTP not found. Please request a new one.'];
        }

        if (Carbon::now()->greaterThan($record->expires_at)) {
            $record->delete();
            return ['success' => false, 'message' => 'OTP expired. Please request a new one.'];
        }

        if ($record->otp !== $otp) {
            return ['success' => false, 'message' => 'Invalid OTP.'];
        }

        $record->update(['is_used' => true]);

        // Store verified phone in Cache for 10 minutes
        Cache::put('otp_verified_' . $phone, true, now()->addMinutes(10));

        return ['success' => true, 'message' => 'OTP verified successfully.'];
    }

    public function isPhoneVerified(string $phone): bool
    {
        return Cache::has('otp_verified_' . $phone);
    }

    public function clearVerified(string $phone): void
    {
        Cache::forget('otp_verified_' . $phone);
    }

    // ─────────────────────────────────────────────
    // voicensms.in API call
    // ─────────────────────────────────────────────
 private function callVoicenSmsApi(string $phone, string $otp): bool
{
    try {
      $payload = [
    'ukey'       => config('services.voicensms.api_key'),
    'senderid'   => config('services.voicensms.sender_id'),
    'msisdn'     => [$phone],
    'message'    => "{$otp} is the OTP for Login Registration valid for 10 mins. Please do not share it with anyone. Netaji Subhash Engineering College. Call Us at 9831817307",
    'args'       => [$otp],   // ← passes OTP as <arg1>
    'filetype'   => 2,
    'language'   => 0,
    'credittype' => 2,
    'templateid' => 0,
    'isrefno'    => true,
];


        $url = 'https://api.voicensms.in/SMSAPI/webresources/CreateSMSCampaignPost';

        Log::info('[VoicenSMS] Sending', [
            'url'   => $url,
            'phone' => $phone,
            'payload' => $payload,
        ]);

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept'       => 'application/json',
        ])->post($url, $payload);

        Log::info('[VoicenSMS] Response', [
            'status' => $response->status(),
            'body'   => $response->body(),
        ]);

        $json = $response->json();

        if (isset($json['status']) && strtolower($json['status']) === 'success') {
            return true;
        }

        Log::warning('[VoicenSMS] Failed', ['response' => $json ?? $response->body()]);
        return false;

    } catch (\Exception $e) {
        Log::error('[VoicenSMS] Exception', ['error' => $e->getMessage()]);
        return false;
    }
}
}