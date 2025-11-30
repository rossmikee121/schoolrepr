<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Fee\StudentFee;
use Laravel\Sanctum\Sanctum;

class PaymentIntegrationTest extends TestCase
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

    public function test_can_create_razorpay_order()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $studentFee = StudentFee::factory()->create([
            'outstanding_amount' => 50000
        ]);

        $response = $this->postJson('/api/payments/create-order', [
            'student_fee_id' => $studentFee->id,
            'amount' => 25000
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => ['order_id', 'amount', 'currency', 'key']
                ]);
    }

    public function test_can_get_outstanding_report()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        StudentFee::factory()->count(3)->create([
            'outstanding_amount' => 10000
        ]);

        $response = $this->getJson('/api/reports/outstanding');

        $response->assertStatus(200)
                ->assertJson(['success' => true])
                ->assertJsonPath('data.total_outstanding', 30000)
                ->assertJsonPath('data.count', 3);
    }

    public function test_can_get_collection_report()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/reports/collection?' . http_build_query([
            'from_date' => '2025-01-01',
            'to_date' => '2025-01-31'
        ]));

        $response->assertStatus(200)
                ->assertJson(['success' => true])
                ->assertJsonStructure([
                    'data' => ['payments', 'summary']
                ]);
    }
}