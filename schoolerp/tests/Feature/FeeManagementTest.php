<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\User\Student;
use App\Models\Fee\FeeHead;
use App\Models\Fee\FeeStructure;
use App\Models\Fee\StudentFee;
use Laravel\Sanctum\Sanctum;

class FeeManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate', ['--path' => 'database/migrations/2024_01_01_000000_create_core_tables']);
        $this->artisan('migrate', ['--path' => 'database/migrations/2024_01_02_000000_create_academic_tables']);
        $this->artisan('migrate', ['--path' => 'database/migrations/2024_01_03_000000_create_fee_tables']);
        
        \Spatie\Permission\Models\Role::create(['name' => 'student']);
        \Spatie\Permission\Models\Role::create(['name' => 'admin']);
    }

    public function test_can_assign_fees_to_students()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $student = Student::factory()->create();
        $feeHead = FeeHead::factory()->create();
        $feeStructure = FeeStructure::factory()->create([
            'fee_head_id' => $feeHead->id,
            'amount' => 50000
        ]);

        $response = $this->postJson('/api/fees/assign', [
            'student_ids' => [$student->id],
            'fee_structure_ids' => [$feeStructure->id]
        ]);

        $response->assertStatus(200)
                ->assertJson(['success' => true]);

        $this->assertDatabaseHas('student_fees', [
            'student_id' => $student->id,
            'fee_structure_id' => $feeStructure->id,
            'total_amount' => 50000,
            'outstanding_amount' => 50000
        ]);
    }

    public function test_can_record_payment()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $student = Student::factory()->create();
        $studentFee = StudentFee::factory()->create([
            'student_id' => $student->id,
            'total_amount' => 50000,
            'final_amount' => 50000,
            'outstanding_amount' => 50000
        ]);

        $response = $this->postJson("/api/students/{$student->id}/payment", [
            'student_fee_id' => $studentFee->id,
            'amount' => 25000,
            'payment_mode' => 'cash',
            'payment_date' => '2025-01-01'
        ]);

        $response->assertStatus(201)
                ->assertJson(['success' => true]);

        $studentFee->refresh();
        $this->assertEquals(25000, $studentFee->paid_amount);
        $this->assertEquals(25000, $studentFee->outstanding_amount);
        $this->assertEquals('partial', $studentFee->status);
    }

    public function test_can_get_outstanding_fees()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $student = Student::factory()->create();
        StudentFee::factory()->create([
            'student_id' => $student->id,
            'outstanding_amount' => 30000
        ]);

        $response = $this->getJson("/api/students/{$student->id}/outstanding");

        $response->assertStatus(200)
                ->assertJson(['success' => true])
                ->assertJsonPath('data.total_outstanding', 30000);
    }
}