<?php

namespace App\Http\Controllers\Api\Attendance;

use App\Http\Controllers\Controller;
use App\Models\Attendance\Attendance;
use App\Models\Academic\Division;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function markAttendance(Request $request): JsonResponse
    {
        $request->validate([
            'division_id' => 'required|exists:divisions,id',
            'attendance_date' => 'required|date',
            'attendance' => 'required|array',
            'attendance.*.student_id' => 'required|exists:students,id',
            'attendance.*.status' => 'required|in:present,absent,late',
            'attendance.*.check_in_time' => 'nullable|date_format:H:i',
            'attendance.*.remarks' => 'nullable|string',
        ]);

        foreach ($request->attendance as $record) {
            Attendance::updateOrCreate(
                [
                    'student_id' => $record['student_id'],
                    'attendance_date' => $request->attendance_date,
                ],
                [
                    'status' => $record['status'],
                    'check_in_time' => $record['check_in_time'] ?? null,
                    'remarks' => $record['remarks'] ?? null,
                    'marked_by' => auth()->id(),
                ]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Attendance marked successfully'
        ]);
    }

    public function getAttendanceReport(Request $request): JsonResponse
    {
        $request->validate([
            'division_id' => 'required|exists:divisions,id',
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
        ]);

        $division = Division::with('students')->find($request->division_id);
        $totalDays = Carbon::parse($request->from_date)->diffInDays(Carbon::parse($request->to_date)) + 1;

        $report = [];
        foreach ($division->students as $student) {
            $attendanceRecords = Attendance::where('student_id', $student->id)
                ->whereBetween('attendance_date', [$request->from_date, $request->to_date])
                ->get();

            $presentDays = $attendanceRecords->where('status', 'present')->count();
            $absentDays = $attendanceRecords->where('status', 'absent')->count();
            $percentage = $totalDays > 0 ? round($presentDays / $totalDays * 100, 2) : 0;

            $report[] = [
                'student' => $student,
                'present_days' => $presentDays,
                'absent_days' => $absentDays,
                'attendance_percentage' => $percentage
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $report
        ]);
    }

    public function getDefaulters(Request $request): JsonResponse
    {
        $threshold = $request->threshold ?? 75;
        $fromDate = $request->from_date ?? Carbon::now()->subMonth()->toDateString();
        $toDate = $request->to_date ?? Carbon::now()->toDateString();

        $totalDays = Carbon::parse($fromDate)->diffInDays(Carbon::parse($toDate)) + 1;

        $defaulters = Attendance::selectRaw('student_id, COUNT(*) as present_days')
            ->where('status', 'present')
            ->whereBetween('attendance_date', [$fromDate, $toDate])
            ->groupBy('student_id')
            ->havingRaw('(COUNT(*) / ?) * 100 < ?', [$totalDays, $threshold])
            ->with('student')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $defaulters
        ]);
    }
}