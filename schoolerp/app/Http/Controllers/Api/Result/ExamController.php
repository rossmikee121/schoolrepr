<?php

namespace App\Http\Controllers\Api\Result;

use App\Http\Controllers\Controller;
use App\Models\Result\Examination;
use App\Models\Result\StudentMark;
use App\Models\Result\Subject;
use App\Services\GradeCalculationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ExamController extends Controller
{
    public function enterMarks(Request $request): JsonResponse
    {
        $request->validate([
            'examination_id' => 'required|exists:examinations,id',
            'marks' => 'required|array',
            'marks.*.student_id' => 'required|exists:students,id',
            'marks.*.subject_id' => 'required|exists:subjects,id',
            'marks.*.marks_obtained' => 'required|numeric|min:0',
            'marks.*.max_marks' => 'required|numeric|min:1',
        ]);

        foreach ($request->marks as $markData) {
            $subject = Subject::find($markData['subject_id']);
            $percentage = ($markData['marks_obtained'] / $markData['max_marks']) * 100;
            
            StudentMark::updateOrCreate(
                [
                    'student_id' => $markData['student_id'],
                    'subject_id' => $markData['subject_id'],
                    'examination_id' => $request->examination_id,
                ],
                [
                    'marks_obtained' => $markData['marks_obtained'],
                    'max_marks' => $markData['max_marks'],
                    'grade' => GradeCalculationService::calculateGrade($percentage),
                    'result' => GradeCalculationService::determineResult(
                        $markData['marks_obtained'], 
                        $subject->passing_marks
                    ),
                ]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Marks entered successfully'
        ]);
    }

    public function approveMarks(Request $request): JsonResponse
    {
        $request->validate([
            'mark_ids' => 'required|array',
            'mark_ids.*' => 'exists:student_marks,id'
        ]);

        StudentMark::whereIn('id', $request->mark_ids)
            ->update(['is_approved' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Marks approved successfully'
        ]);
    }

    public function getResults(Request $request): JsonResponse
    {
        $query = StudentMark::with(['student', 'subject', 'examination'])
            ->approved();

        if ($request->student_id) {
            $query->where('student_id', $request->student_id);
        }

        if ($request->examination_id) {
            $query->where('examination_id', $request->examination_id);
        }

        $results = $query->get();

        return response()->json([
            'success' => true,
            'data' => $results
        ]);
    }

    public function generateMarksheet(Request $request): JsonResponse
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'examination_id' => 'required|exists:examinations,id'
        ]);

        $marks = StudentMark::with(['subject', 'examination'])
            ->where('student_id', $request->student_id)
            ->where('examination_id', $request->examination_id)
            ->approved()
            ->get();

        $totalMarks = $marks->sum('marks_obtained');
        $totalMaxMarks = $marks->sum('max_marks');
        $percentage = $totalMaxMarks > 0 ? ($totalMarks / $totalMaxMarks) * 100 : 0;

        $marksheetData = [
            'marks' => $marks,
            'total_marks' => $totalMarks,
            'total_max_marks' => $totalMaxMarks,
            'percentage' => round($percentage, 2),
            'overall_grade' => GradeCalculationService::calculateGrade($percentage),
            'result' => $marks->where('result', 'fail')->count() > 0 ? 'FAIL' : 'PASS'
        ];

        return response()->json([
            'success' => true,
            'data' => $marksheetData
        ]);
    }
}