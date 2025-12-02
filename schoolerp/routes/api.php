<?php

/**
 * =============================================================================
 * SCHOOL ERP API ROUTES - Main Entry Point for All API Endpoints
 * =============================================================================
 * 
 * This file defines ALL API routes for the School ERP system.
 * Think of this as a "menu" that tells the system which URL goes to which function.
 * 
 * HOW IT WORKS:
 * 1. When someone visits /api/students, Laravel looks here to find the right controller
 * 2. Routes are organized by functionality (students, fees, attendance, etc.)
 * 3. Most routes require authentication (login) except login itself
 * 
 * DATABASE CONNECTIONS:
 * - All routes connect to the main PostgreSQL database
 * - Each controller handles specific database tables
 * - Authentication uses 'users' table
 * 
 * FOR INTERNS: 
 * - Route::post() = Create new data (like adding a student)
 * - Route::get() = Read/fetch data (like getting student list)
 * - Route::put() = Update existing data (like editing student info)
 * - Route::delete() = Remove data (like deleting a student)
 * =============================================================================
 */

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Academic\StudentController;
use App\Http\Controllers\Api\Academic\DivisionController;
use App\Http\Controllers\Api\Fee\FeeController;

/*
|--------------------------------------------------------------------------
| API Routes Configuration
|--------------------------------------------------------------------------
| All routes here are prefixed with /api/ automatically by Laravel
| Example: Route::post('/login') becomes /api/login in the browser
*/

// ========================================
// AUTHENTICATION ROUTES (No login required)
// ========================================
// These routes handle user login/logout and don't need authentication
// Database Tables Used: users, personal_access_tokens

Route::post('/login', [AuthController::class, 'login']);           // POST /api/login - User login
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');  // POST /api/logout - User logout
Route::get('/user', [AuthController::class, 'user'])->middleware('auth:sanctum');       // GET /api/user - Get current user info

// ========================================
// PROTECTED ROUTES (Login required for all below)
// ========================================
// All routes inside this group require valid authentication token
// If user is not logged in, they get "401 Unauthorized" error

Route::middleware(['auth:sanctum'])->group(function () {
    
    // ========================================
    // DEPARTMENT MANAGEMENT
    // ========================================
    // Handles Commerce, Science, Arts departments
    // Database Table: departments
    // Creates all CRUD routes: GET, POST, PUT, DELETE /api/departments
    Route::apiResource('departments', \App\Http\Controllers\Api\Academic\DepartmentController::class);
    
    // ========================================
    // PROGRAM MANAGEMENT  
    // ========================================
    // Handles degree programs like B.Com, B.Sc, MBA, etc.
    // Database Table: programs
    // Creates routes: GET, POST, PUT, DELETE /api/programs
    Route::apiResource('programs', \App\Http\Controllers\Api\Academic\ProgramController::class);
    
    // ========================================
    // ACADEMIC SESSION MANAGEMENT
    // ========================================
    // Handles academic years like 2024-25, semesters, etc.
    // Database Table: academic_sessions
    // Creates routes: GET, POST, PUT, DELETE /api/academic-sessions
    Route::apiResource('academic-sessions', \App\Http\Controllers\Api\Academic\AcademicSessionController::class);
    
    // ========================================
    // STUDENT MANAGEMENT
    // ========================================
    // Main student operations - add, edit, view, delete students
    // Database Tables: students, divisions, programs, academic_sessions
    // Special middleware checks if division has space before adding student
    Route::apiResource('students', \App\Http\Controllers\Api\Academic\StudentController::class)
        ->middleware('check.division.capacity');  // Prevents adding students to full divisions
    
    // GET /api/students/{id}/profile - Get detailed student profile with all related data
    Route::get('students/{student}/profile', [\App\Http\Controllers\Api\Academic\StudentController::class, 'profile']);
    
    // ========================================
    // GUARDIAN/PARENT MANAGEMENT
    // ========================================
    // Manages student parents/guardians information
    // Database Table: student_guardians
    // Nested resource: /api/students/{student_id}/guardians
    Route::apiResource('students.guardians', \App\Http\Controllers\Api\Academic\GuardianController::class)
        ->except(['create', 'edit']);  // Excludes form routes (API doesn't need them)
    
    // ========================================
    // DOCUMENT MANAGEMENT
    // ========================================
    // Handles student photo and signature uploads
    // Files stored in storage/app/public/students/{id}/
    Route::post('students/{student}/documents/photo', [\App\Http\Controllers\Api\Academic\DocumentController::class, 'uploadPhoto']);
    Route::post('students/{student}/documents/signature', [\App\Http\Controllers\Api\Academic\DocumentController::class, 'uploadSignature']);
    Route::get('students/{student}/documents', [\App\Http\Controllers\Api\Academic\DocumentController::class, 'getDocuments']);
    Route::delete('students/{student}/documents/photo', [\App\Http\Controllers\Api\Academic\DocumentController::class, 'deletePhoto']);
    Route::delete('students/{student}/documents/signature', [\App\Http\Controllers\Api\Academic\DocumentController::class, 'deleteSignature']);
    
    // ========================================
    // DIVISION MANAGEMENT (Class Sections)
    // ========================================
    // Manages class divisions like FY-A, SY-B, TY-C etc.
    // Database Table: divisions
    Route::apiResource('divisions', DivisionController::class);
    
    // GET /api/divisions/{id}/students - Get all students in a specific division
    Route::get('divisions/{division}/students', [DivisionController::class, 'students']);
    
    // ========================================
    // EXAMINATION MANAGEMENT
    // ========================================
    // Handles exam creation, marks entry, result processing
    // Database Tables: examinations, student_marks, subjects
    Route::apiResource('exams', \App\Http\Controllers\Api\Result\ExamController::class);
    Route::apiResource('examinations', \App\Http\Controllers\Api\Result\ExaminationController::class);
    
    // ========================================
    // FEE MANAGEMENT
    // ========================================
    // Handles all fee-related operations
    // Database Tables: student_fees, fee_payments, fee_structures, fee_heads
    
    // POST /api/fees/assign - Assign fee structure to students
    Route::post('fees/assign', [\App\Http\Controllers\Api\Fee\FeeController::class, 'assignFees']);
    
    // POST /api/students/{id}/payment - Record manual fee payment
    Route::post('students/{student}/payment', [\App\Http\Controllers\Api\Fee\FeeController::class, 'recordPayment']);
    
    // GET /api/students/{id}/outstanding - Get pending fee amount for student
    Route::get('students/{student}/outstanding', [\App\Http\Controllers\Api\Fee\FeeController::class, 'outstanding']);
    
    // ========================================
    // SCHOLARSHIP MANAGEMENT
    // ========================================
    // Handles scholarships and fee discounts (SC/ST, Merit-based)
    // Database Tables: scholarships, student_scholarships
    
    // POST /api/scholarships/assign - Assign scholarship to student
    Route::post('scholarships/assign', [\App\Http\Controllers\Api\Fee\ScholarshipController::class, 'assignScholarship']);
    
    // POST /api/students/{id}/calculate-fee - Calculate final fee after scholarships
    Route::post('students/{student}/calculate-fee', [\App\Http\Controllers\Api\Fee\ScholarshipController::class, 'calculateFee']);
    
    // ========================================
    // ONLINE PAYMENTS (Razorpay Integration)
    // ========================================
    // Handles online fee payments through Razorpay gateway
    // Database Tables: fee_payments (stores payment status)
    
    // POST /api/payments/create-order - Create Razorpay payment order
    Route::post('payments/create-order', [\App\Http\Controllers\Api\Fee\PaymentController::class, 'createOrder']);
    
    // POST /api/payments/verify - Verify payment after Razorpay callback
    Route::post('payments/verify', [\App\Http\Controllers\Api\Fee\PaymentController::class, 'verifyPayment']);
    
    // ========================================
    // FEE REPORTS
    // ========================================
    // Generate various fee-related reports for management
    // Database Tables: student_fees, fee_payments (with complex queries)
    
    // GET /api/reports/outstanding - Students with pending fees
    Route::get('reports/outstanding', [\App\Http\Controllers\Api\Fee\ReportController::class, 'outstandingReport']);
    
    // GET /api/reports/collection - Daily/monthly fee collection summary
    Route::get('reports/collection', [\App\Http\Controllers\Api\Fee\ReportController::class, 'collectionReport']);
    
    // GET /api/reports/defaulters - Students who haven't paid fees on time
    Route::get('reports/defaulters', [\App\Http\Controllers\Api\Fee\ReportController::class, 'defaulterReport']);
    
    // ========================================
    // LABORATORY MANAGEMENT
    // ========================================
    // Handles lab batching for practical sessions
    // Database Tables: labs, lab_sessions, lab_batches
    
    // POST /api/labs/create-batches - Auto-create lab batches for students
    Route::post('labs/create-batches', [\App\Http\Controllers\Api\Lab\LabController::class, 'createBatches']);
    
    // GET /api/labs/sessions - Get all lab sessions
    Route::get('labs/sessions', [\App\Http\Controllers\Api\Lab\LabController::class, 'getSessions']);
    
    // POST /api/labs/sessions/{id}/attendance - Mark lab attendance
    Route::post('labs/sessions/{session}/attendance', [\App\Http\Controllers\Api\Lab\LabController::class, 'markAttendance']);
    
    // POST /api/labs/reassign-student - Move student between lab batches
    Route::post('labs/reassign-student', [\App\Http\Controllers\Api\Lab\LabController::class, 'reassignStudent']);
    
    // ========================================
    // RESULTS & EXAMINATIONS
    // ========================================
    // Handles exam marks entry, approval, and result generation
    // Database Tables: student_marks, examinations, subjects
    
    // POST /api/exams/enter-marks - Teachers enter student marks
    Route::post('exams/enter-marks', [\App\Http\Controllers\Api\Result\ExamController::class, 'enterMarks']);
    
    // POST /api/exams/approve-marks - HOD/Principal approve entered marks
    Route::post('exams/approve-marks', [\App\Http\Controllers\Api\Result\ExamController::class, 'approveMarks']);
    
    // GET /api/exams/results - Get exam results with CGPA calculations
    Route::get('exams/results', [\App\Http\Controllers\Api\Result\ExamController::class, 'getResults']);
    
    // GET /api/exams/marksheet - Generate PDF marksheet for students
    Route::get('exams/marksheet', [\App\Http\Controllers\Api\Result\ExamController::class, 'generateMarksheet']);
    
    // ========================================
    // ATTENDANCE & TIMETABLE
    // ========================================
    // Handles daily attendance and class scheduling
    // Database Tables: attendance, timetables
    
    // POST /api/attendance/mark - Mark daily attendance for students
    Route::post('attendance/mark', [\App\Http\Controllers\Api\Attendance\AttendanceController::class, 'markAttendance']);
    
    // GET /api/attendance/report - Get attendance percentage reports
    Route::get('attendance/report', [\App\Http\Controllers\Api\Attendance\AttendanceController::class, 'getAttendanceReport']);
    
    // GET /api/attendance/defaulters - Students with low attendance
    Route::get('attendance/defaulters', [\App\Http\Controllers\Api\Attendance\AttendanceController::class, 'getDefaulters']);
    
    // Full CRUD for timetables
    Route::apiResource('timetables', \App\Http\Controllers\Api\Attendance\TimetableController::class);
    
    // GET /api/timetables/view - View formatted timetable
    Route::get('timetables/view', [\App\Http\Controllers\Api\Attendance\TimetableController::class, 'getTimetable']);
    
    // ========================================
    // DYNAMIC REPORTING SYSTEM
    // ========================================
    // Advanced report builder - users can create custom reports
    // Database Tables: report_templates, report_exports + all other tables for data
    Route::prefix('reports')->group(function () {
        
        // REPORT BUILDER - Drag & Drop Report Creation
        // GET /api/reports/models - Get available data models (Student, Fee, etc.)
        Route::get('models', [\App\Http\Controllers\Api\Reports\ReportBuilderController::class, 'getAvailableModels']);
        
        // GET /api/reports/columns - Get available columns for selected model
        Route::get('columns', [\App\Http\Controllers\Api\Reports\ReportBuilderController::class, 'getAvailableColumns']);
        
        // POST /api/reports/build - Build custom report with filters
        Route::post('build', [\App\Http\Controllers\Api\Reports\ReportBuilderController::class, 'buildReport']);
        
        // POST /api/reports/export - Export report to Excel/PDF
        Route::post('export', [\App\Http\Controllers\Api\Reports\ReportBuilderController::class, 'exportReport']);
        
        // GET /api/reports/exports/{id}/status - Check export progress
        Route::get('exports/{exportId}/status', [\App\Http\Controllers\Api\Reports\ReportBuilderController::class, 'getExportStatus']);
        
        // GET /api/reports/exports/{id}/download - Download completed export
        Route::get('exports/{exportId}/download', [\App\Http\Controllers\Api\Reports\ReportBuilderController::class, 'downloadExport']);
        
        // REPORT TEMPLATES - Pre-built report templates
        // Full CRUD for saving/loading report configurations
        Route::apiResource('templates', \App\Http\Controllers\Api\Reports\ReportTemplateController::class);
        
        // GET /api/reports/templates/category/{name} - Get templates by category
        Route::get('templates/category/{category}', [\App\Http\Controllers\Api\Reports\ReportTemplateController::class, 'getByCategory']);
    });
    
    // ========================================
    // LIBRARY MANAGEMENT
    // ========================================
    // Handles book lending, returns, and library operations
    // Database Tables: books, book_issues, students
    Route::prefix('library')->group(function () {
        
        // GET /api/library/books - Get all available books
        Route::get('books', [\App\Http\Controllers\Api\Library\LibraryController::class, 'getBooks']);
        
        // POST /api/library/issue - Issue book to student
        Route::post('issue', [\App\Http\Controllers\Api\Library\LibraryController::class, 'issueBook']);
        
        // POST /api/library/return - Return book and calculate fines
        Route::post('return', [\App\Http\Controllers\Api\Library\LibraryController::class, 'returnBook']);
        
        // GET /api/library/student/{id}/issues - Get books issued to specific student
        Route::get('student/{studentId}/issues', [\App\Http\Controllers\Api\Library\LibraryController::class, 'getStudentIssues']);
        
        // GET /api/library/overdue - Get overdue books with fine calculations
        Route::get('overdue', [\App\Http\Controllers\Api\Library\LibraryController::class, 'getOverdueBooks']);
    });
    
    // ========================================
    // HR & PAYROLL MANAGEMENT
    // ========================================
    // Handles staff management and salary processing
    // Database Tables: staff, salary_structures, salary_payments
    Route::prefix('hr')->group(function () {
        
        // GET /api/hr/staff - Get all staff members
        Route::get('staff', [\App\Http\Controllers\Api\HR\HRController::class, 'getStaff']);
        
        // POST /api/hr/salaries/generate - Generate monthly salaries
        Route::post('salaries/generate', [\App\Http\Controllers\Api\HR\HRController::class, 'generateSalaries']);
        
        // POST /api/hr/salaries/process-payment - Process salary payments
        Route::post('salaries/process-payment', [\App\Http\Controllers\Api\HR\HRController::class, 'processSalaryPayment']);
        
        // GET /api/hr/salaries/report - Get salary reports
        Route::get('salaries/report', [\App\Http\Controllers\Api\HR\HRController::class, 'getSalaryReport']);
        
        // GET /api/hr/salary-structures - Get salary structure templates
        Route::get('salary-structures', [\App\Http\Controllers\Api\HR\HRController::class, 'getSalaryStructures']);
    });
    
});

// ========================================
// WEBHOOK ROUTES (No Authentication Required)
// ========================================
// These routes are called by external services, not users
// They don't need login because they use other security methods

// POST /api/webhooks/razorpay - Razorpay payment gateway callback
// Called automatically when payment is completed/failed
// Uses Razorpay signature verification for security
Route::post('/webhooks/razorpay', [\App\Http\Controllers\Api\Fee\PaymentController::class, 'webhook']);

/**
 * =============================================================================
 * END OF API ROUTES
 * =============================================================================
 * 
 * SUMMARY FOR INTERNS:
 * - Total 54+ API endpoints covering complete school management
 * - All routes except login/webhooks require authentication
 * - Routes are organized by functionality (students, fees, attendance, etc.)
 * - Each route connects to specific database tables through controllers
 * - Controllers contain the actual business logic
 * - Models represent database tables
 * - Services handle complex calculations and business rules
 * 
 * NEXT STEPS:
 * 1. Check Controllers to see how each route works
 * 2. Check Models to understand database structure  
 * 3. Check Services for business logic
 * =============================================================================
 */
