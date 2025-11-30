<?php

namespace App\Http\Controllers\Api\Academic;

use App\Http\Controllers\Controller;
use App\Models\User\Student;
use App\Models\User;
use App\Services\RollNumberService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StudentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Student::with(['program', 'division', 'academicSession'])
            ->active();

        // Apply filters based on user role and permissions
        if ($request->has('program_id')) {
            $query->byProgram($request->program_id);
        }

        if ($request->has('division_id')) {
            $query->byDivision($request->division_id);
        }

        if ($request->has('academic_year')) {
            $query->byAcademicYear($request->academic_year);
        }

        $students = $query->paginate(25);

        return response()->json([
            'success' => true,
            'data' => $students
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'first_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'last_name' => 'required|string|max:100',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:male,female,other',
            'mobile_number' => 'nullable|string|max:15',
            'email' => 'nullable|email|unique:students,email',
            'program_id' => 'required|exists:programs,id',
            'academic_year' => 'required|string|max:20',
            'division_id' => 'required|exists:divisions,id',
            'academic_session_id' => 'required|exists:academic_sessions,id',
            'admission_date' => 'required|date',
            'category' => 'nullable|in:general,obc,sc,st,vjnt,nt,sbc',
        ]);

        return DB::transaction(function () use ($request) {
            // Create user account for student
            $user = User::create([
                'name' => trim($request->first_name . ' ' . $request->last_name),
                'email' => $request->email ?: $request->first_name . '.' . $request->last_name . '@student.local',
                'password' => Hash::make('student123'), // Default password
            ]);

            $user->assignRole('student');

            // Generate roll number
            $rollNumber = RollNumberService::generate(
                $request->program_id,
                $request->academic_year,
                $request->division_name ?? 'A' // Default to A if not provided
            );

            // Generate admission number
            $admissionNumber = 'ADM' . date('Y') . str_pad(Student::count() + 1, 4, '0', STR_PAD_LEFT);

            // Create student record
            $student = Student::create(array_merge($request->all(), [
                'user_id' => $user->id,
                'admission_number' => $admissionNumber,
                'roll_number' => $rollNumber,
            ]));

            $student->load(['program', 'division', 'academicSession', 'user']);

            return response()->json([
                'success' => true,
                'message' => 'Student created successfully',
                'data' => $student
            ], 201);
        });
    }

    public function show(Student $student): JsonResponse
    {
        $student->load(['program', 'division', 'academicSession', 'guardians', 'user']);

        return response()->json([
            'success' => true,
            'data' => $student
        ]);
    }

    public function update(Request $request, Student $student): JsonResponse
    {
        $request->validate([
            'first_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'last_name' => 'required|string|max:100',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:male,female,other',
            'mobile_number' => 'nullable|string|max:15',
            'email' => 'nullable|email|unique:students,email,' . $student->id,
            'student_status' => 'nullable|in:active,graduated,dropped,suspended,tc_issued',
        ]);

        $student->update($request->all());

        // Update user name if changed
        if ($request->has(['first_name', 'last_name'])) {
            $student->user->update([
                'name' => trim($request->first_name . ' ' . $request->last_name)
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Student updated successfully',
            'data' => $student->load(['program', 'division', 'academicSession'])
        ]);
    }
}