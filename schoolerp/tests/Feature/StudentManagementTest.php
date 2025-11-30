<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\User\Student;
use App\Models\Academic\Department;
use App\Models\Academic\Program;
use App\Models\Academic\AcademicSession;
use App\Models\Academic\Division;
use Laravel\Sanctum\Sanctum;

class StudentManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate', ['--path' => 'database/migrations/2024_01_01_000000_create_core_tables']);
        $this->artisan('migrate', ['--path' => 'database/migrations/2024_01_02_000000_create_academic_tables']);
        
        // Create required roles
        \Spatie\Permission\Models\Role::create(['name' => 'student']);
        \Spatie\Permission\Models\Role::create(['name' => 'admin']);
    }

    public function test_can_create_student_with_roll_number_generation()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Create required data
        $department = Department::factory()->create(['code' => 'COM']);
        $program = Program::factory()->create([
            'department_id' => $department->id,
            'code' => 'BCOM'
        ]);
        $session = AcademicSession::factory()->create(['session_name' => '2025-26']);
        $division = Division::factory()->create(['division_name' => 'A']);

        $studentData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'date_of_birth' => '2000-01-01',
            'gender' => 'male',
            'program_id' => $program->id,
            'academic_year' => '2025-26',
            'division_id' => $division->id,
            'academic_session_id' => $session->id,
            'admission_date' => '2025-06-01',
        ];

        $response = $this->postJson('/api/students', $studentData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'id',
                        'roll_number',
                        'admission_number',
                        'first_name',
                        'last_name'
                    ]
                ]);

        $this->assertDatabaseHas('students', [
            'first_name' => 'John',
            'last_name' => 'Doe'
        ]);
    }

    public function test_division_capacity_validation()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Create division with capacity of 1
        $department = Department::factory()->create();
        $program = Program::factory()->create(['department_id' => $department->id]);
        $session = AcademicSession::factory()->create();
        $division = Division::factory()->create(['max_students' => 1]);

        // Create one student (fills capacity)
        Student::factory()->create([
            'division_id' => $division->id,
            'student_status' => 'active'
        ]);

        // Try to add another student
        $studentData = [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'date_of_birth' => '2000-01-01',
            'gender' => 'female',
            'program_id' => $program->id,
            'academic_year' => '2025-26',
            'division_id' => $division->id,
            'academic_session_id' => $session->id,
            'admission_date' => '2025-06-01',
        ];

        $response = $this->postJson('/api/students', $studentData);

        $response->assertStatus(422)
                ->assertJson([
                    'success' => false,
                    'message' => 'Division capacity exceeded. Maximum students: 1'
                ]);
    }
}