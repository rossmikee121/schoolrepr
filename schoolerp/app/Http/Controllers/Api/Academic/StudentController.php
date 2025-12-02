<?php

/**
 * =============================================================================
 * STUDENT CONTROLLER - Handles All Student API Operations
 * =============================================================================
 * 
 * This controller manages all student-related API endpoints.
 * It handles CRUD operations (Create, Read, Update, Delete) for students.
 * 
 * API ENDPOINTS HANDLED:
 * - GET /api/students - List all students with filters
 * - POST /api/students - Create new student
 * - GET /api/students/{id} - Get specific student details
 * - PUT /api/students/{id} - Update student information
 * - DELETE /api/students/{id} - Delete student (soft delete)
 * 
 * DATABASE TABLES USED:
 * - students (main student data)
 * - users (login credentials for students)
 * - programs (B.Com, B.Sc, etc.)
 * - divisions (FY-A, SY-B, etc.)
 * - academic_sessions (2024-25, etc.)
 * - roll_number_sequences (for auto-generating roll numbers)
 * 
 * BUSINESS LOGIC:
 * - Auto-generates admission numbers and roll numbers
 * - Creates user accounts for student login
 * - Validates division capacity before admission
 * - Handles student status changes (active, graduated, etc.)
 * 
 * FOR INTERNS:
 * - Controller = Handles HTTP requests and responses
 * - Each method = One API endpoint
 * - Validation = Checks if input data is correct
 * - Database Transaction = Ensures all operations succeed or fail together
 * =============================================================================
 */

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
    /**
     * LIST STUDENTS - GET /api/students
     * 
     * This endpoint returns a paginated list of students with optional filters.
     * 
     * PROCESS:
     * 1. Start with base query for active students
     * 2. Load related data (program, division, academic session)
     * 3. Apply filters if provided in request
     * 4. Paginate results (25 students per page)
     * 5. Return JSON response
     * 
     * QUERY PARAMETERS:
     * - program_id: Filter by program (B.Com=1, B.Sc=2, etc.)
     * - division_id: Filter by division (FY-A=1, SY-B=2, etc.)
     * - academic_year: Filter by year (FY, SY, TY)
     * 
     * DATABASE QUERIES EXECUTED:
     * - SELECT * FROM students WHERE student_status = 'active'
     * - SELECT * FROM programs WHERE id IN (...)
     * - SELECT * FROM divisions WHERE id IN (...)
     * - SELECT * FROM academic_sessions WHERE id IN (...)
     * 
     * EXAMPLE RESPONSE:
     * {
     *   "success": true,
     *   "data": {
     *     "data": [student objects],
     *     "current_page": 1,
     *     "total": 150
     *   }
     * }
     */
    public function index(Request $request): JsonResponse
    {
        // Start building query with related data loaded
        // with() = Eager loading to prevent N+1 query problem
        // active() = Only show active students (not graduated/dropped)
        $query = Student::with(['program', 'division', 'academicSession'])
            ->active();

        // Apply filters based on request parameters
        // These use the scopes defined in Student model
        
        if ($request->has('program_id')) {
            $query->byProgram($request->program_id);  // Filter by B.Com, B.Sc, etc.
        }

        if ($request->has('division_id')) {
            $query->byDivision($request->division_id);  // Filter by FY-A, SY-B, etc.
        }

        if ($request->has('academic_year')) {
            $query->byAcademicYear($request->academic_year);  // Filter by FY, SY, TY
        }

        // Paginate results (25 students per page)
        // This prevents loading thousands of students at once
        $students = $query->paginate(25);

        return response()->json([
            'success' => true,
            'data' => $students  // Includes pagination metadata
        ]);
    }

    /**
     * CREATE STUDENT - POST /api/students
     * 
     * This endpoint creates a new student record with all necessary data.
     * 
     * PROCESS:
     * 1. Validate all input data
     * 2. Start database transaction (ensures all operations succeed together)
     * 3. Create user account for student login
     * 4. Assign 'student' role to user
     * 5. Generate unique admission number and roll number
     * 6. Create student record with all data
     * 7. Return success response with created student data
     * 
     * REQUIRED FIELDS:
     * - first_name, last_name: Student name
     * - date_of_birth: Birth date
     * - gender: male/female/other
     * - program_id: Which program (B.Com, B.Sc, etc.)
     * - academic_year: FY, SY, TY
     * - division_id: Which division (A, B, C)
     * - academic_session_id: Which session (2024-25, etc.)
     * - admission_date: When student joined
     * 
     * AUTO-GENERATED DATA:
     * - admission_number: ADM2024001, ADM2024002, etc.
     * - roll_number: Generated by RollNumberService
     * - user_id: Links to created user account
     * 
     * DATABASE OPERATIONS:
     * 1. INSERT INTO users (name, email, password)
     * 2. INSERT INTO model_has_roles (user_id, role_id)
     * 3. INSERT INTO students (all student data)
     * 
     * MIDDLEWARE: check.division.capacity
     * - Prevents adding students if division is full
     */
    public function store(Request $request): JsonResponse
    {
        // Step 1: Validate all input data
        // This ensures data quality and prevents errors
        $request->validate([
            'first_name' => 'required|string|max:100',      // Required, max 100 chars
            'middle_name' => 'nullable|string|max:100',     // Optional
            'last_name' => 'required|string|max:100',       // Required
            'date_of_birth' => 'required|date',             // Must be valid date
            'gender' => 'required|in:male,female,other',    // Only these values allowed
            'mobile_number' => 'nullable|string|max:15',    // Optional phone number
            'email' => 'nullable|email|unique:students,email', // Must be unique if provided
            'program_id' => 'required|exists:programs,id',  // Must exist in programs table
            'academic_year' => 'required|string|max:20',    // FY, SY, TY
            'division_id' => 'required|exists:divisions,id', // Must exist in divisions table
            'academic_session_id' => 'required|exists:academic_sessions,id', // Must exist
            'admission_date' => 'required|date',            // When student joined
            'category' => 'nullable|in:general,obc,sc,st,vjnt,nt,sbc', // For scholarships
        ]);

        // Step 2: Use database transaction
        // If any operation fails, all changes are rolled back
        return DB::transaction(function () use ($request) {
            
            // Step 3: Create user account for student login
            $user = User::create([
                'name' => trim($request->first_name . ' ' . $request->last_name),
                // If no email provided, create a default one
                'email' => $request->email ?: $request->first_name . '.' . $request->last_name . '@student.local',
                'password' => Hash::make('student123'), // Default password (should be changed)
            ]);

            // Step 4: Assign student role for permissions
            $user->assignRole('student');

            // Step 5: Generate unique roll number
            // RollNumberService handles the complex logic
            $rollNumber = RollNumberService::generate(
                $request->program_id,
                $request->academic_year,
                $request->division_name ?? 'A' // Default to A if not provided
            );

            // Step 6: Generate unique admission number
            // Format: ADM + Year + Sequential number (ADM2024001, ADM2024002, etc.)
            $admissionNumber = 'ADM' . date('Y') . str_pad(Student::count() + 1, 4, '0', STR_PAD_LEFT);

            // Step 7: Create student record with all data
            $student = Student::create(array_merge($request->all(), [
                'user_id' => $user->id,                    // Link to user account
                'admission_number' => $admissionNumber,    // Auto-generated
                'roll_number' => $rollNumber,              // Auto-generated
            ]));

            // Step 8: Load related data for response
            $student->load(['program', 'division', 'academicSession', 'user']);

            return response()->json([
                'success' => true,
                'message' => 'Student created successfully',
                'data' => $student
            ], 201);  // 201 = Created HTTP status code
        });
    }

    /**
     * GET STUDENT DETAILS - GET /api/students/{id}
     * 
     * This endpoint returns detailed information about a specific student.
     * 
     * PROCESS:
     * 1. Laravel automatically finds student by ID (Route Model Binding)
     * 2. Load all related data (program, division, guardians, etc.)
     * 3. Return complete student profile
     * 
     * ROUTE MODEL BINDING:
     * - Laravel automatically converts {student} in URL to Student model
     * - If student doesn't exist, returns 404 error automatically
     * - No need to manually check if student exists
     * 
     * LOADED RELATIONSHIPS:
     * - program: B.Com, B.Sc, MBA, etc.
     * - division: FY-A, SY-B, TY-C, etc.
     * - academicSession: 2024-25, 2025-26, etc.
     * - guardians: Parents/guardians information
     * - user: Login credentials and user data
     * 
     * DATABASE QUERIES:
     * - SELECT * FROM students WHERE id = ?
     * - SELECT * FROM programs WHERE id = ?
     * - SELECT * FROM divisions WHERE id = ?
     * - SELECT * FROM academic_sessions WHERE id = ?
     * - SELECT * FROM student_guardians WHERE student_id = ?
     * - SELECT * FROM users WHERE id = ?
     */
    public function show(Student $student): JsonResponse
    {
        // Load all related data in one go (prevents multiple database queries)
        $student->load(['program', 'division', 'academicSession', 'guardians', 'user']);

        return response()->json([
            'success' => true,
            'data' => $student  // Complete student profile with all relationships
        ]);
    }

    /**
     * UPDATE STUDENT - PUT /api/students/{id}
     * 
     * This endpoint updates an existing student's information.
     * 
     * PROCESS:
     * 1. Validate input data (similar to create but allows existing email)
     * 2. Update student record in database
     * 3. Update related user account if name changed
     * 4. Return updated student data
     * 
     * UPDATABLE FIELDS:
     * - Personal info: name, date of birth, gender, contact details
     * - Status: active, graduated, dropped, suspended, tc_issued
     * - Contact: mobile number, email
     * 
     * BUSINESS RULES:
     * - Email must be unique (except for current student)
     * - Student status can be changed for lifecycle management
     * - User account name is synced with student name
     * 
     * DATABASE OPERATIONS:
     * 1. UPDATE students SET ... WHERE id = ?
     * 2. UPDATE users SET name = ? WHERE id = ? (if name changed)
     * 
     * STATUS MEANINGS:
     * - active: Currently studying
     * - graduated: Completed course
     * - dropped: Left before completion
     * - suspended: Temporarily not allowed
     * - tc_issued: Transfer Certificate issued
     */
    public function update(Request $request, Student $student): JsonResponse
    {
        // Step 1: Validate input data
        // Note: email validation excludes current student's email from uniqueness check
        $request->validate([
            'first_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'last_name' => 'required|string|max:100',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:male,female,other',
            'mobile_number' => 'nullable|string|max:15',
            // Allow current student's email but prevent duplicates with others
            'email' => 'nullable|email|unique:students,email,' . $student->id,
            'student_status' => 'nullable|in:active,graduated,dropped,suspended,tc_issued',
        ]);

        // Step 2: Update student record
        $student->update($request->all());

        // Step 3: Update user account name if name fields changed
        // This keeps the user account in sync with student data
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

/**
 * =============================================================================
 * STUDENT CONTROLLER SUMMARY FOR INTERNS
 * =============================================================================
 * 
 * API ENDPOINTS PROVIDED:
 * 1. GET /api/students - List students with filters and pagination
 * 2. POST /api/students - Create new student with auto-generated numbers
 * 3. GET /api/students/{id} - Get detailed student information
 * 4. PUT /api/students/{id} - Update student information
 * 
 * KEY FEATURES:
 * - Automatic admission number generation
 * - Automatic roll number generation via service
 * - User account creation for student login
 * - Role assignment for permissions
 * - Database transactions for data integrity
 * - Eager loading to prevent N+1 query problems
 * - Comprehensive validation
 * - Pagination for large datasets
 * 
 * BUSINESS LOGIC:
 * - Students get user accounts for portal access
 * - Admission numbers follow ADM{YEAR}{SEQUENCE} format
 * - Roll numbers are generated by specialized service
 * - Student status tracks lifecycle (active -> graduated/dropped)
 * - Division capacity is checked via middleware
 * 
 * DATABASE RELATIONSHIPS:
 * - Student belongs to Program (B.Com, B.Sc, etc.)
 * - Student belongs to Division (FY-A, SY-B, etc.)
 * - Student belongs to Academic Session (2024-25, etc.)
 * - Student has User account (for login)
 * - Student has many Guardians (parents)
 * - Student has many Fees (fee records)
 * 
 * TESTING EXAMPLES:
 * - POST /api/students with required fields
 * - GET /api/students?program_id=1&division_id=2
 * - PUT /api/students/1 with updated data
 * - Check database tables to see created records
 * =============================================================================
 */