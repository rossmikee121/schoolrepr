<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Academic\Division;
use App\Models\User\Student;

class CheckDivisionCapacity
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->isMethod('post') && $request->has('division_id')) {
            $division = Division::find($request->division_id);
            
            if ($division) {
                $currentCount = Student::where('division_id', $division->id)
                    ->where('student_status', 'active')
                    ->whereNull('deleted_at')
                    ->count();

                if ($currentCount >= $division->max_students) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Division capacity exceeded. Maximum students: ' . $division->max_students
                    ], 422);
                }
            }
        }

        return $next($request);
    }
}