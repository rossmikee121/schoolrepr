<?php

namespace App\Http\Controllers\Api\Lab;

use App\Http\Controllers\Controller;
use App\Models\Lab\Lab;
use App\Models\Lab\LabSession;
use App\Models\Academic\Division;
use App\Services\LabBatchingService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class LabController extends Controller
{
    public function createBatches(Request $request): JsonResponse
    {
        $request->validate([
            'division_id' => 'required|exists:divisions,id',
            'lab_id' => 'required|exists:labs,id',
            'subject_name' => 'required|string|max:100',
            'session_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'instructor_id' => 'nullable|exists:users,id',
        ]);

        $division = Division::find($request->division_id);
        $lab = Lab::find($request->lab_id);

        $result = LabBatchingService::createBatches(
            $division,
            $lab,
            $request->subject_name,
            $request->only(['session_date', 'start_time', 'end_time', 'instructor_id'])
        );

        return response()->json([
            'success' => true,
            'message' => "Created {$result['batches']} batches for {$result['total_students']} students",
            'data' => $result
        ]);
    }

    public function getSessions(Request $request): JsonResponse
    {
        $query = LabSession::with(['lab', 'division', 'instructor', 'batches.student']);

        if ($request->lab_id) {
            $query->where('lab_id', $request->lab_id);
        }

        if ($request->division_id) {
            $query->where('division_id', $request->division_id);
        }

        if ($request->date) {
            $query->whereDate('session_date', $request->date);
        }

        $sessions = $query->get();

        return response()->json([
            'success' => true,
            'data' => $sessions
        ]);
    }

    public function markAttendance(Request $request, LabSession $session): JsonResponse
    {
        $request->validate([
            'attendance' => 'required|array',
            'attendance.*.student_id' => 'required|exists:students,id',
            'attendance.*.is_present' => 'required|boolean',
            'attendance.*.remarks' => 'nullable|string',
        ]);

        foreach ($request->attendance as $record) {
            $session->batches()
                ->where('student_id', $record['student_id'])
                ->update([
                    'is_present' => $record['is_present'],
                    'remarks' => $record['remarks'] ?? null,
                ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Attendance marked successfully'
        ]);
    }

    public function reassignStudent(Request $request): JsonResponse
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'from_session_id' => 'required|exists:lab_sessions,id',
            'to_session_id' => 'required|exists:lab_sessions,id',
        ]);

        $success = LabBatchingService::reassignStudent(
            $request->student_id,
            $request->from_session_id,
            $request->to_session_id
        );

        if ($success) {
            return response()->json([
                'success' => true,
                'message' => 'Student reassigned successfully'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Cannot reassign student - target batch is full'
        ], 400);
    }
}