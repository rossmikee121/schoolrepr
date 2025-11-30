<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Academic\Department;
use Laravel\Sanctum\Sanctum;

class DepartmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate', ['--path' => 'database/migrations/2024_01_01_000000_create_core_tables']);
    }

    public function test_authenticated_user_can_view_departments()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        Department::factory()->create([
            'name' => 'Commerce',
            'code' => 'COM',
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/departments');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        '*' => [
                            'id',
                            'name',
                            'code',
                            'is_active'
                        ]
                    ]
                ]);
    }

    public function test_unauthenticated_user_cannot_view_departments()
    {
        $response = $this->getJson('/api/departments');

        $response->assertStatus(401);
    }
}