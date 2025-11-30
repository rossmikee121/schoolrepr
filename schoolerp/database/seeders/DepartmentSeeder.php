<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Academic\Department;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            [
                'name' => 'Commerce',
                'code' => 'COM',
                'description' => 'Department of Commerce and Management Studies',
                'is_active' => true,
            ],
            [
                'name' => 'Science',
                'code' => 'SCI',
                'description' => 'Department of Science and Technology',
                'is_active' => true,
            ],
            [
                'name' => 'Management',
                'code' => 'MGT',
                'description' => 'Department of Management Studies',
                'is_active' => true,
            ],
            [
                'name' => 'Arts',
                'code' => 'ARTS',
                'description' => 'Department of Arts and Humanities',
                'is_active' => true,
            ],
        ];

        foreach ($departments as $department) {
            Department::create($department);
        }
    }
}