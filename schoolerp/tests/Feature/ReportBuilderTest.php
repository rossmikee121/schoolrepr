<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Student;
use App\Models\Department;
use App\Models\Reports\ReportTemplate;

class ReportBuilderTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_can_get_available_models()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/reports/models');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data'
            ]);
    }

    public function test_can_get_available_columns()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/reports/columns?model=students');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data'
            ]);
    }

    public function test_can_build_simple_report()
    {
        Department::factory()->create();
        Student::factory()->count(3)->create();

        $configuration = [
            'base_model' => 'students',
            'columns' => [
                ['field' => 'id', 'alias' => 'Student ID'],
                ['field' => 'first_name', 'alias' => 'First Name'],
                ['field' => 'last_name', 'alias' => 'Last Name']
            ],
            'limit' => 10
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/reports/build', $configuration);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
                'total',
                'configuration'
            ]);
    }

    public function test_can_build_report_with_filters()
    {
        Department::factory()->create();
        Student::factory()->create(['first_name' => 'John']);
        Student::factory()->create(['first_name' => 'Jane']);

        $configuration = [
            'base_model' => 'students',
            'columns' => [
                ['field' => 'first_name', 'alias' => 'First Name']
            ],
            'filters' => [
                'logic' => 'and',
                'conditions' => [
                    [
                        'column' => 'first_name',
                        'operator' => '=',
                        'value' => 'John'
                    ]
                ]
            ]
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/reports/build', $configuration);

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
    }

    public function test_can_create_report_template()
    {
        $templateData = [
            'name' => 'Student List Report',
            'description' => 'Basic student listing',
            'category' => 'student',
            'configuration' => [
                'base_model' => 'students',
                'columns' => [
                    ['field' => 'first_name', 'alias' => 'First Name'],
                    ['field' => 'last_name', 'alias' => 'Last Name']
                ]
            ],
            'is_public' => false
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/reports/templates', $templateData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'name',
                    'category',
                    'configuration'
                ]
            ]);
    }

    public function test_can_get_templates_by_category()
    {
        ReportTemplate::factory()->create([
            'category' => 'student',
            'created_by' => $this->user->id,
            'is_public' => true
        ]);

        ReportTemplate::factory()->create([
            'category' => 'fee',
            'created_by' => $this->user->id,
            'is_public' => true
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/reports/templates/category/student');

        $response->assertStatus(200);
        $templates = $response->json('data');
        $this->assertCount(1, $templates);
        $this->assertEquals('student', $templates[0]['category']);
    }
}