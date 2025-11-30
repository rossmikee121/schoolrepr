<?php

namespace App\Services;

class GradeCalculationService
{
    public static function calculateGrade(float $percentage): string
    {
        if ($percentage >= 90) return 'A+';
        if ($percentage >= 80) return 'A';
        if ($percentage >= 70) return 'B+';
        if ($percentage >= 60) return 'B';
        if ($percentage >= 50) return 'C+';
        if ($percentage >= 40) return 'C';
        return 'F';
    }

    public static function calculateCGPA(array $marks): float
    {
        $totalCredits = 0;
        $totalGradePoints = 0;

        foreach ($marks as $mark) {
            $gradePoint = self::getGradePoint($mark['percentage']);
            $credits = $mark['credits'] ?? 1;
            
            $totalGradePoints += $gradePoint * $credits;
            $totalCredits += $credits;
        }

        return $totalCredits > 0 ? round($totalGradePoints / $totalCredits, 2) : 0;
    }

    private static function getGradePoint(float $percentage): float
    {
        if ($percentage >= 90) return 10.0;
        if ($percentage >= 80) return 9.0;
        if ($percentage >= 70) return 8.0;
        if ($percentage >= 60) return 7.0;
        if ($percentage >= 50) return 6.0;
        if ($percentage >= 40) return 5.0;
        return 0.0;
    }

    public static function determineResult(float $marksObtained, float $passingMarks): string
    {
        if ($marksObtained >= $passingMarks) {
            return 'pass';
        }
        return 'fail';
    }
}