<?php

namespace App\Http\Controllers\Api\V1\Live;

use App\Http\Controllers\Controller;
use App\Models\LiveSession;
use App\Models\LiveAttendance;
use Illuminate\Http\Request;

class LiveController extends Controller
{
    public function index(Request $request, int $courseId)
    {
        $sessions = LiveSession::where('course_id', $courseId)
            ->orderBy('start_at', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Live sessions retrieved successfully.',
            'data' => $sessions
        ]);
    }

    public function show(LiveSession $session)
    {
        return response()->json([
            'success' => true,
            'message' => 'Live session retrieved successfully.',
            'data' => $session
        ]);
    }

    public function join(Request $request, LiveSession $session)
    {
        $user = $request->user();

        // Track user attendance initialization
        $attendance = LiveAttendance::create([
            'live_session_id' => $session->id,
            'student_id' => $user->id,
            'joined_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Live session joined successfully.',
            'data' => [
                'attendance_id' => $attendance->id,
                'meeting_url' => $session->meeting_url,
                'meeting_id' => $session->meeting_id,
            ]
        ]);
    }
}
