<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Academic\AcademicSession;

class AcademicSessionSeeder extends Seeder
{
    public function run(): void
    {
        $sessions = [
            [
                'session_name' => '2024-25',
                'start_date' => '2024-06-01',
                'end_date' => '2025-05-31',
                'is_current' => false,
                'is_active' => true,
            ],
            [
                'session_name' => '2025-26',
                'start_date' => '2025-06-01',
                'end_date' => '2026-05-31',
                'is_current' => true,
                'is_active' => true,
            ],
            [
                'session_name' => '2026-27',
                'start_date' => '2026-06-01',
                'end_date' => '2027-05-31',
                'is_current' => false,
                'is_active' => true,
            ],
        ];

        foreach ($sessions as $session) {
            AcademicSession::create($session);
        }
    }
}