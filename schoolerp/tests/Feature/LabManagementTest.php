<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\User\Student;
use App\Models\Academic\Division;
use App\Models\Lab\Lab;
use App\Models\Lab\LabSession;
use Laravel\Sanctum\Sanctum;

class LabManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate', ['--path' => 'database/migrations/2024_01_01_000000_create_core_tables']);
        $this->artisan('migrate', ['--path' => 'database/migrations/2024_01_02_000000_create_academic_tables']);
        $this->artisan('migrate', ['--path' => 'database/migrations/2024_01_04_000000_create_lab_tables']);
        
        \Spatie\Permission\Models\Role::create(['name' => 'student']);
        \Spatie\Permission\Models\Role::create(['name' => 'admin']);
    }

    public function test_can_create_lab_batches()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $division = Division::factory()->create();
        $lab = Lab::factory()->create(['capacity' => 25]);
        
        // Create 60 students in division
        Student::factory()->count(60)->create([
            'division_id' => $division->id,
            'student_status' => 'active'
        ]);

        $response = $this->postJson('/api/labs/create-batches', [
            'division_id' => $division->id,
            'lab_id' => $lab->id,
            'subject_name' => 'Computer Programming',
            'session_date' => '2025-01-15',
            'start_time' => '09:00',
            'end_time' => '11:00'
        ]);

        $response->assertStatus(200)
                ->assertJson(['success' => true])
                ->assertJsonPath('data.batches', 3) // 60 students / 25 capacity = 3 batches
                ->assertJsonPath('data.total_students', 60);

        $this->assertEquals(3, LabSession::count());
    }

    public function test_can_mark_attendance()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $session = LabSession::factory()->create();
        $students = Student::factory()->count(2)->create();

        // Create lab batches
        foreach ($students as $student) {
            $session->batches()->create(['student_id' => $student->id]);
        }

        $response = $this->postJson("/api/labs/sessions/{$session->id}/attendance", [
            'attendance' => [
                ['student_id' => $students[0]->id, 'is_present' => true],
                ['student_id' => $students[1]->id, 'is_present' => false, 'remarks' => 'Absent']
            ]
        ]);

        $response->assertStatus(200)
                ->assertJson(['success' => true]);

        $this->assertTrue($session->batches()->where('student_id', $students[0]->id)->first()->is_present);
        $this->assertFalse($session->batches()->where('student_id', $students[1]->id)->first()->is_present);
    }

    public function test_can_get_lab_sessions()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $lab = Lab::factory()->create();
        LabSession::factory()->count(3)->create(['lab_id' => $lab->id]);

        $response = $this->getJson("/api/labs/sessions?lab_id={$lab->id}");

        $response->assertStatus(200)
                ->assertJson(['success' => true])
                ->assertJsonCount(3, 'data');
    }
}