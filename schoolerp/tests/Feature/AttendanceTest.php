<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\User\Student;
use App\Models\Academic\Division;
use App\Models\Attendance\Attendance;
use Laravel\Sanctum\Sanctum;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
        
        \Spatie\Permission\Models\Role::create(['name' => 'student']);
        \Spatie\Permission\Models\Role::create(['name' => 'admin']);
    }

    public function test_can_mark_attendance()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $division = Division::factory()->create();
        $students = Student::factory()->count(2)->create(['division_id' => $division->id]);

        $response = $this->postJson('/api/attendance/mark', [
            'division_id' => $division->id,
            'attendance_date' => '2025-01-15',
            'attendance' => [
                ['student_id' => $students[0]->id, 'status' => 'present', 'check_in_time' => '09:00'],
                ['student_id' => $students[1]->id, 'status' => 'absent', 'remarks' => 'Sick leave']
            ]
        ]);

        $response->assertStatus(200)
                ->assertJson(['success' => true]);

        $this->assertDatabaseHas('attendance', [
            'student_id' => $students[0]->id,
            'status' => 'present'
        ]);

        $this->assertDatabaseHas('attendance', [
            'student_id' => $students[1]->id,
            'status' => 'absent'
        ]);
    }

    public function test_can_get_attendance_report()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $division = Division::factory()->create();
        $student = Student::factory()->create(['division_id' => $division->id]);

        // Create attendance records
        Attendance::factory()->create([
            'student_id' => $student->id,
            'attendance_date' => '2025-01-15',
            'status' => 'present'
        ]);

        Attendance::factory()->create([
            'student_id' => $student->id,
            'attendance_date' => '2025-01-16',
            'status' => 'absent'
        ]);

        $response = $this->getJson('/api/attendance/report?' . http_build_query([
            'division_id' => $division->id,
            'from_date' => '2025-01-15',
            'to_date' => '2025-01-16'
        ]));

        $response->assertStatus(200)
                ->assertJson(['success' => true])
                ->assertJsonStructure(['data' => [['student', 'present_days', 'absent_days', 'attendance_percentage']]]);
    }

    public function test_can_get_defaulters()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $student = Student::factory()->create();

        // Create low attendance record
        Attendance::factory()->create([
            'student_id' => $student->id,
            'status' => 'present'
        ]);

        $response = $this->getJson('/api/attendance/defaulters?threshold=80');

        $response->assertStatus(200)
                ->assertJson(['success' => true]);
    }
}