<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\User\Student;
use App\Models\Result\Subject;
use App\Models\Result\Examination;
use App\Models\Result\StudentMark;
use Laravel\Sanctum\Sanctum;

class ExaminationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate', ['--path' => 'database/migrations/2024_01_01_000000_create_core_tables']);
        $this->artisan('migrate', ['--path' => 'database/migrations/2024_01_02_000000_create_academic_tables']);
        $this->artisan('migrate', ['--path' => 'database/migrations/2024_01_05_000000_create_result_tables']);
        
        \Spatie\Permission\Models\Role::create(['name' => 'student']);
        \Spatie\Permission\Models\Role::create(['name' => 'admin']);
    }

    public function test_can_enter_marks()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $student = Student::factory()->create();
        $subject = Subject::factory()->create(['max_marks' => 100, 'passing_marks' => 40]);
        $exam = Examination::factory()->create();

        $response = $this->postJson('/api/exams/enter-marks', [
            'examination_id' => $exam->id,
            'marks' => [
                [
                    'student_id' => $student->id,
                    'subject_id' => $subject->id,
                    'marks_obtained' => 85,
                    'max_marks' => 100
                ]
            ]
        ]);

        $response->assertStatus(200)
                ->assertJson(['success' => true]);

        $this->assertDatabaseHas('student_marks', [
            'student_id' => $student->id,
            'subject_id' => $subject->id,
            'marks_obtained' => 85,
            'grade' => 'A',
            'result' => 'pass'
        ]);
    }

    public function test_can_generate_marksheet()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $student = Student::factory()->create();
        $exam = Examination::factory()->create();
        
        StudentMark::factory()->create([
            'student_id' => $student->id,
            'examination_id' => $exam->id,
            'marks_obtained' => 85,
            'max_marks' => 100,
            'is_approved' => true
        ]);

        $response = $this->getJson("/api/exams/marksheet?student_id={$student->id}&examination_id={$exam->id}");

        $response->assertStatus(200)
                ->assertJson(['success' => true])
                ->assertJsonStructure([
                    'data' => [
                        'marks', 'total_marks', 'percentage', 'overall_grade', 'result'
                    ]
                ]);
    }

    public function test_can_approve_marks()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $mark = StudentMark::factory()->create(['is_approved' => false]);

        $response = $this->postJson('/api/exams/approve-marks', [
            'mark_ids' => [$mark->id]
        ]);

        $response->assertStatus(200)
                ->assertJson(['success' => true]);

        $this->assertTrue($mark->fresh()->is_approved);
    }
}