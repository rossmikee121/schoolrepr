<?php

namespace Database\Factories;

use App\Models\Result\StudentMark;
use App\Models\User\Student;
use App\Models\Result\Subject;
use App\Models\Result\Examination;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudentMarkFactory extends Factory
{
    protected $model = StudentMark::class;

    public function definition(): array
    {
        $maxMarks = 100;
        $marksObtained = $this->faker->numberBetween(30, 100);
        $percentage = ($marksObtained / $maxMarks) * 100;
        
        return [
            'student_id' => Student::factory(),
            'subject_id' => Subject::factory(),
            'examination_id' => Examination::factory(),
            'marks_obtained' => $marksObtained,
            'max_marks' => $maxMarks,
            'grade' => $this->getGrade($percentage),
            'result' => $marksObtained >= 40 ? 'pass' : 'fail',
            'is_approved' => false,
        ];
    }

    private function getGrade(float $percentage): string
    {
        if ($percentage >= 90) return 'A+';
        if ($percentage >= 80) return 'A';
        if ($percentage >= 70) return 'B+';
        if ($percentage >= 60) return 'B';
        if ($percentage >= 50) return 'C+';
        if ($percentage >= 40) return 'C';
        return 'F';
    }
}