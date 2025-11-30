<?php

namespace App\Http\Controllers\Api\Attendance;

use App\Http\Controllers\Controller;
use App\Models\Attendance\Timetable;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TimetableController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'division_id' => 'required|exists:divisions,id',
            'subject_id' => 'required|exists:subjects,id',
            'day_of_week' => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'room' => 'nullable|string|max:50',
            'teacher_id' => 'nullable|exists:users,id',
        ]);

        $timetable = Timetable::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Timetable entry created successfully',
            'data' => $timetable->load(['division', 'subject', 'teacher'])
        ], 201);
    }

    public function getTimetable(Request $request): JsonResponse
    {
        $request->validate([
            'division_id' => 'nullable|exists:divisions,id',
            'teacher_id' => 'nullable|exists:users,id',
            'day_of_week' => 'nullable|in:monday,tuesday,wednesday,thursday,friday,saturday',
        ]);

        $query = Timetable::with(['division', 'subject', 'teacher'])->active();

        if ($request->division_id) {
            $query->where('division_id', $request->division_id);
        }

        if ($request->teacher_id) {
            $query->where('teacher_id', $request->teacher_id);
        }

        if ($request->day_of_week) {
            $query->where('day_of_week', $request->day_of_week);
        }

        $timetable = $query->orderBy('day_of_week')->orderBy('start_time')->get();

        return response()->json([
            'success' => true,
            'data' => $timetable
        ]);
    }

    public function update(Request $request, Timetable $timetable): JsonResponse
    {
        $request->validate([
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
            'room' => 'nullable|string|max:50',
            'teacher_id' => 'nullable|exists:users,id',
        ]);

        $timetable->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Timetable updated successfully',
            'data' => $timetable->load(['division', 'subject', 'teacher'])
        ]);
    }

    public function destroy(Timetable $timetable): JsonResponse
    {
        $timetable->delete();

        return response()->json([
            'success' => true,
            'message' => 'Timetable entry deleted successfully'
        ]);
    }
}