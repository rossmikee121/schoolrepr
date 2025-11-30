<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class RollNumberService
{
    public static function generate($programId, $academicYear, $divisionName)
    {
        return DB::transaction(function() use ($programId, $academicYear, $divisionName) {
            $sequence = DB::table('roll_number_sequences')
                ->where([
                    'program_id' => $programId, 
                    'academic_year' => $academicYear, 
                    'division_name' => $divisionName
                ])
                ->lockForUpdate()
                ->first();
            
            $nextNumber = $sequence ? $sequence->last_number + 1 : 1;
            
            DB::table('roll_number_sequences')->updateOrInsert(
                [
                    'program_id' => $programId, 
                    'academic_year' => $academicYear, 
                    'division_name' => $divisionName
                ],
                ['last_number' => $nextNumber]
            );
            
            // Get program code
            $program = DB::table('programs')->where('id', $programId)->first();
            
            return sprintf('%s/%s/%s/%03d', 
                $academicYear, 
                $program->code, 
                $divisionName, 
                $nextNumber
            );
        });
    }
}
