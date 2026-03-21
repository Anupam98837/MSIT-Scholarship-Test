<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StudentDoubtSubmissionController extends Controller
{
    // ---------------------------------------------------------------
    // Actor — reads from request attributes set by your auth middleware
    // ---------------------------------------------------------------
    private function actor(Request $request): array
    {
        return [
            'role' => $request->attributes->get('auth_role'),
            'type' => $request->attributes->get('auth_tokenable_type'),
            'id'   => (int) ($request->attributes->get('auth_tokenable_id') ?? 0),
        ];
    }

    // ---------------------------------------------------------------
    // GET /api/student/doubt-subjects
    // Returns the full subjects config for the blade JS (SUBJECTS var)
    // ---------------------------------------------------------------
    public function subjects(): JsonResponse
    {
        return response()->json([
            'data' => config('doubts.subjects', []),
        ]);
    }

    // ---------------------------------------------------------------
    // GET /api/student/doubt-submissions
    // List all submissions for the logged-in student
    // ---------------------------------------------------------------
    public function index(Request $request): JsonResponse
    {
        $actor = $this->actor($request);

        $submissions = DB::table('doubt_submissions')
            ->where('student_id', $actor['id'])
            ->orderByDesc('submitted_at')
            ->get();

        return response()->json([
            'data' => $submissions,
        ]);
    }

    // ---------------------------------------------------------------
    // GET /api/student/doubt-submissions/{uuid}
    // Get a single submission — student can only view their own
    // ---------------------------------------------------------------
    public function show(Request $request, string $uuid): JsonResponse
    {
        $actor = $this->actor($request);

        $submission = DB::table('doubt_submissions')
            ->where('uuid', $uuid)
            ->where('student_id', $actor['id'])
            ->first();

        if (! $submission) {
            return response()->json(['message' => 'Submission not found.'], 404);
        }

        $submission->topics = json_decode($submission->topics, true);

        return response()->json([
            'data' => $submission,
        ]);
    }

    // ---------------------------------------------------------------
    // POST /api/student/doubt-submissions
    // Create today's submission or update if one already exists
    // for this student + subject today (upsert by submitted_date)
    // ---------------------------------------------------------------
    public function store(Request $request): JsonResponse
    {
        $allowedSubjects = array_keys(config('doubts.subjects', []));

        $request->validate([
            'subject'    => ['required', 'string', \Illuminate\Validation\Rule::in($allowedSubjects)],
            'select_all' => ['sometimes', 'boolean'],
            'topics'     => ['required_unless:select_all,true', 'array'],
            'topics.*'   => ['array'],
            'topics.*.*' => ['integer', 'in:0,1'],
            'notes'      => ['sometimes', 'nullable', 'string', 'max:1000'],
        ]);

        $actor   = $this->actor($request);
        $subject = $request->input('subject');
        $today   = now()->toDateString();

        $notes = $request->filled('notes') ? trim($request->input('notes')) : null;
        $topics = $request->boolean('select_all')
            ? $this->selectAllTopics($subject)
            : $request->input('topics', []);

        // check if submission already exists for today
        $existing = DB::table('doubt_submissions')
            ->where('student_id', $actor['id'])
            ->where('subject', $subject)
            ->where('submitted_date', $today)
            ->first();

        if ($existing) {
            DB::table('doubt_submissions')
                ->where('id', $existing->id)
                ->update([
                    'topics'       => json_encode($topics),
                    'notes'        => $notes,
                    'submitted_at' => now(),
                ]);

            $submission         = DB::table('doubt_submissions')->where('id', $existing->id)->first();
            $submission->topics = json_decode($submission->topics, true);

            return response()->json([
                'message'    => 'Submission updated.',
                'submission' => $submission,
            ], 200);
        }

        $uuid = (string) Str::uuid();

        DB::table('doubt_submissions')->insert([
            'uuid'         => $uuid,
            'student_id'   => $actor['id'],
            'subject'      => $subject,
            'topics'       => json_encode($topics),
            'notes'        => $notes,
            'submitted_at' => now(),
        ]);

        $submission         = DB::table('doubt_submissions')->where('uuid', $uuid)->first();
        $submission->topics = json_decode($submission->topics, true);

        return response()->json([
            'message'    => 'Submission created.',
            'submission' => $submission,
        ], 201);
    }

    // ---------------------------------------------------------------
    // select_all helper — marks every subtopic as 1 for the subject
    // driven by config/doubts.php
    // ---------------------------------------------------------------
    private function selectAllTopics(string $subject): array
    {
        $allTopics   = [];
        $subjectData = config("doubts.subjects.{$subject}.chapters", []);

        foreach ($subjectData as $chapterKey => $chapter) {
            $allTopics[$chapterKey] = [];

            foreach (($chapter['subtopics'] ?? []) as $subtopicKey => $subtopicLabel) {
                $allTopics[$chapterKey][$subtopicKey] = 1;
            }
        }

        return $allTopics;
    }
}