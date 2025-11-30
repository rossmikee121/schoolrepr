<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\User\Student;
use App\Models\User\StudentGuardian;
use Laravel\Sanctum\Sanctum;

class GuardianManagementTest extends TestCase
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

    public function test_can_add_guardian_to_student()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $student = Student::factory()->create();

        $guardianData = [
            'guardian_type' => 'father',
            'full_name' => 'John Doe Sr.',
            'occupation' => 'Engineer',
            'annual_income' => 500000,
            'mobile_number' => '9876543210',
            'email' => 'john.sr@example.com',
            'is_primary_contact' => true,
        ];

        $response = $this->postJson("/api/students/{$student->id}/guardians", $guardianData);

        $response->assertStatus(201)
                ->assertJson([
                    'success' => true,
                    'message' => 'Guardian added successfully'
                ]);

        $this->assertDatabaseHas('student_guardians', [
            'student_id' => $student->id,
            'guardian_type' => 'father',
            'full_name' => 'John Doe Sr.',
            'is_primary_contact' => true,
        ]);
    }

    public function test_can_list_student_guardians()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $student = Student::factory()->create();
        
        // Create guardians
        StudentGuardian::factory()->create([
            'student_id' => $student->id,
            'guardian_type' => 'father',
            'full_name' => 'Father Name'
        ]);
        
        StudentGuardian::factory()->create([
            'student_id' => $student->id,
            'guardian_type' => 'mother',
            'full_name' => 'Mother Name'
        ]);

        $response = $this->getJson("/api/students/{$student->id}/guardians");

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true
                ])
                ->assertJsonCount(2, 'data');
    }

    public function test_primary_contact_is_unique_per_student()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $student = Student::factory()->create();

        // Create first guardian as primary
        StudentGuardian::factory()->create([
            'student_id' => $student->id,
            'guardian_type' => 'father',
            'is_primary_contact' => true,
        ]);

        // Add second guardian as primary - should remove primary from first
        $guardianData = [
            'guardian_type' => 'mother',
            'full_name' => 'Mother Name',
            'is_primary_contact' => true,
        ];

        $response = $this->postJson("/api/students/{$student->id}/guardians", $guardianData);

        $response->assertStatus(201);

        // Check that only one guardian is primary
        $this->assertEquals(1, $student->guardians()->where('is_primary_contact', true)->count());
        $this->assertEquals('mother', $student->guardians()->where('is_primary_contact', true)->first()->guardian_type);
    }
}