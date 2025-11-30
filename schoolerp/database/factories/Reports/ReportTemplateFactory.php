<?php

namespace Database\Factories\Reports;

use App\Models\Reports\ReportTemplate;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReportTemplateFactory extends Factory
{
    protected $model = ReportTemplate::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence(3),
            'description' => $this->faker->sentence(),
            'category' => $this->faker->randomElement(['student', 'fee', 'academic', 'administrative']),
            'configuration' => [
                'base_model' => 'students',
                'columns' => [
                    ['field' => 'first_name', 'alias' => 'First Name'],
                    ['field' => 'last_name', 'alias' => 'Last Name']
                ]
            ],
            'created_by' => User::factory(),
            'is_public' => $this->faker->boolean(),
            'is_active' => true
        ];
    }

    public function public(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_public' => true,
        ]);
    }

    public function private(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_public' => false,
        ]);
    }

    public function category(string $category): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => $category,
        ]);
    }
}