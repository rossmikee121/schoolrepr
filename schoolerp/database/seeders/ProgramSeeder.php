<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Academic\Program;
use App\Models\Academic\Department;

class ProgramSeeder extends Seeder
{
    public function run(): void
    {
        $commerce = Department::where('code', 'COM')->first();
        $science = Department::where('code', 'SCI')->first();
        $management = Department::where('code', 'MGT')->first();
        $arts = Department::where('code', 'ARTS')->first();

        $programs = [
            // Commerce Programs
            [
                'name' => 'Bachelor of Commerce',
                'short_name' => 'B.Com',
                'code' => 'BCOM',
                'university_affiliation' => 'Savitribai Phule Pune University',
                'department_id' => $commerce->id,
                'duration_years' => 3,
                'total_semesters' => 6,
                'program_type' => 'undergraduate',
                'default_grade_scale_name' => 'SPPU 10-Point',
            ],
            [
                'name' => 'Master of Commerce',
                'short_name' => 'M.Com',
                'code' => 'MCOM',
                'university_affiliation' => 'Savitribai Phule Pune University',
                'department_id' => $commerce->id,
                'duration_years' => 2,
                'total_semesters' => 4,
                'program_type' => 'postgraduate',
                'default_grade_scale_name' => 'SPPU 10-Point',
            ],
            [
                'name' => 'Bachelor of Business Administration',
                'short_name' => 'BBA',
                'code' => 'BBA',
                'university_affiliation' => 'Savitribai Phule Pune University',
                'department_id' => $management->id,
                'duration_years' => 3,
                'total_semesters' => 6,
                'program_type' => 'undergraduate',
                'default_grade_scale_name' => 'SPPU 10-Point',
            ],
            
            // Science Programs
            [
                'name' => 'Bachelor of Science in Computer Science',
                'short_name' => 'B.Sc CS',
                'code' => 'BSCCS',
                'university_affiliation' => 'Savitribai Phule Pune University',
                'department_id' => $science->id,
                'duration_years' => 3,
                'total_semesters' => 6,
                'program_type' => 'undergraduate',
                'default_grade_scale_name' => 'SPPU 10-Point',
            ],
            [
                'name' => 'Bachelor of Science in Cyber Security',
                'short_name' => 'B.Sc Cyber Security',
                'code' => 'BSCCYBER',
                'university_affiliation' => 'Savitribai Phule Pune University',
                'department_id' => $science->id,
                'duration_years' => 3,
                'total_semesters' => 6,
                'program_type' => 'undergraduate',
                'default_grade_scale_name' => 'SPPU 10-Point',
            ],
            [
                'name' => 'Bachelor of Science in Animation',
                'short_name' => 'B.Sc Animation',
                'code' => 'BSCANIM',
                'university_affiliation' => 'Savitribai Phule Pune University',
                'department_id' => $science->id,
                'duration_years' => 3,
                'total_semesters' => 6,
                'program_type' => 'undergraduate',
                'default_grade_scale_name' => 'SPPU 10-Point',
            ],
            
            // Management Programs
            [
                'name' => 'Master of Business Administration',
                'short_name' => 'MBA',
                'code' => 'MBA',
                'university_affiliation' => 'Savitribai Phule Pune University',
                'department_id' => $management->id,
                'duration_years' => 2,
                'total_semesters' => 4,
                'program_type' => 'postgraduate',
                'default_grade_scale_name' => 'SPPU 10-Point',
            ],
            
            // Arts Programs
            [
                'name' => 'Bachelor of Arts',
                'short_name' => 'BA',
                'code' => 'BA',
                'university_affiliation' => 'Savitribai Phule Pune University',
                'department_id' => $arts->id,
                'duration_years' => 3,
                'total_semesters' => 6,
                'program_type' => 'undergraduate',
                'default_grade_scale_name' => 'SPPU 10-Point',
            ],
        ];

        foreach ($programs as $program) {
            Program::create($program);
        }
    }
}