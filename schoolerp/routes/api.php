<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Academic\StudentController;
use App\Http\Controllers\Api\Academic\DivisionController;
use App\Http\Controllers\Api\Fee\FeeController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Authentication Routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/user', [AuthController::class, 'user'])->middleware('auth:sanctum');

// Protected Routes
Route::middleware(['auth:sanctum'])->group(function () {
    
    // Department Management
    Route::apiResource('departments', \App\Http\Controllers\Api\Academic\DepartmentController::class);
    
    // Student Management
    Route::apiResource('students', \App\Http\Controllers\Api\Academic\StudentController::class)
        ->middleware('check.division.capacity');
    Route::get('students/{student}/profile', [\App\Http\Controllers\Api\Academic\StudentController::class, 'profile']);
    
    // Guardian Management
    Route::apiResource('students.guardians', \App\Http\Controllers\Api\Academic\GuardianController::class)
        ->except(['create', 'edit']);
    
    // Document Management
    Route::post('students/{student}/documents/photo', [\App\Http\Controllers\Api\Academic\DocumentController::class, 'uploadPhoto']);
    Route::post('students/{student}/documents/signature', [\App\Http\Controllers\Api\Academic\DocumentController::class, 'uploadSignature']);
    Route::get('students/{student}/documents', [\App\Http\Controllers\Api\Academic\DocumentController::class, 'getDocuments']);
    Route::delete('students/{student}/documents/photo', [\App\Http\Controllers\Api\Academic\DocumentController::class, 'deletePhoto']);
    Route::delete('students/{student}/documents/signature', [\App\Http\Controllers\Api\Academic\DocumentController::class, 'deleteSignature']);
    
    // Division Management
    Route::apiResource('divisions', DivisionController::class);
    Route::get('divisions/{division}/students', [DivisionController::class, 'students']);
    
    // Fee Management
    Route::post('fees/assign', [\App\Http\Controllers\Api\Fee\FeeController::class, 'assignFees']);
    Route::post('students/{student}/payment', [\App\Http\Controllers\Api\Fee\FeeController::class, 'recordPayment']);
    Route::get('students/{student}/outstanding', [\App\Http\Controllers\Api\Fee\FeeController::class, 'outstanding']);
    
    // Scholarship Management
    Route::post('scholarships/assign', [\App\Http\Controllers\Api\Fee\ScholarshipController::class, 'assignScholarship']);
    Route::post('students/{student}/calculate-fee', [\App\Http\Controllers\Api\Fee\ScholarshipController::class, 'calculateFee']);
    
    // Online Payments
    Route::post('payments/create-order', [\App\Http\Controllers\Api\Fee\PaymentController::class, 'createOrder']);
    Route::post('payments/verify', [\App\Http\Controllers\Api\Fee\PaymentController::class, 'verifyPayment']);
    
    // Fee Reports
    Route::get('reports/outstanding', [\App\Http\Controllers\Api\Fee\ReportController::class, 'outstandingReport']);
    Route::get('reports/collection', [\App\Http\Controllers\Api\Fee\ReportController::class, 'collectionReport']);
    Route::get('reports/defaulters', [\App\Http\Controllers\Api\Fee\ReportController::class, 'defaulterReport']);
    
    // Lab Management
    Route::post('labs/create-batches', [\App\Http\Controllers\Api\Lab\LabController::class, 'createBatches']);
    Route::get('labs/sessions', [\App\Http\Controllers\Api\Lab\LabController::class, 'getSessions']);
    Route::post('labs/sessions/{session}/attendance', [\App\Http\Controllers\Api\Lab\LabController::class, 'markAttendance']);
    Route::post('labs/reassign-student', [\App\Http\Controllers\Api\Lab\LabController::class, 'reassignStudent']);
    
    // Results & Examinations
    Route::post('exams/enter-marks', [\App\Http\Controllers\Api\Result\ExamController::class, 'enterMarks']);
    Route::post('exams/approve-marks', [\App\Http\Controllers\Api\Result\ExamController::class, 'approveMarks']);
    Route::get('exams/results', [\App\Http\Controllers\Api\Result\ExamController::class, 'getResults']);
    Route::get('exams/marksheet', [\App\Http\Controllers\Api\Result\ExamController::class, 'generateMarksheet']);
    
    // Attendance & Timetable
    Route::post('attendance/mark', [\App\Http\Controllers\Api\Attendance\AttendanceController::class, 'markAttendance']);
    Route::get('attendance/report', [\App\Http\Controllers\Api\Attendance\AttendanceController::class, 'getAttendanceReport']);
    Route::get('attendance/defaulters', [\App\Http\Controllers\Api\Attendance\AttendanceController::class, 'getDefaulters']);
    Route::apiResource('timetables', \App\Http\Controllers\Api\Attendance\TimetableController::class);
    Route::get('timetables/view', [\App\Http\Controllers\Api\Attendance\TimetableController::class, 'getTimetable']);
    
    // Dynamic Reporting System
    Route::prefix('reports')->group(function () {
        // Report Builder
        Route::get('models', [\App\Http\Controllers\Api\Reports\ReportBuilderController::class, 'getAvailableModels']);
        Route::get('columns', [\App\Http\Controllers\Api\Reports\ReportBuilderController::class, 'getAvailableColumns']);
        Route::post('build', [\App\Http\Controllers\Api\Reports\ReportBuilderController::class, 'buildReport']);
        Route::post('export', [\App\Http\Controllers\Api\Reports\ReportBuilderController::class, 'exportReport']);
        Route::get('exports/{exportId}/status', [\App\Http\Controllers\Api\Reports\ReportBuilderController::class, 'getExportStatus']);
        Route::get('exports/{exportId}/download', [\App\Http\Controllers\Api\Reports\ReportBuilderController::class, 'downloadExport']);
        
        // Report Templates
        Route::apiResource('templates', \App\Http\Controllers\Api\Reports\ReportTemplateController::class);
        Route::get('templates/category/{category}', [\App\Http\Controllers\Api\Reports\ReportTemplateController::class, 'getByCategory']);
    });
    
});

// Webhook Routes (no authentication)
Route::post('/webhooks/razorpay', [\App\Http\Controllers\Api\Fee\PaymentController::class, 'webhook']);
