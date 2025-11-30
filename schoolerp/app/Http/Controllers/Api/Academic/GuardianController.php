<?php

namespace App\Http\Controllers\Api\Academic;

use App\Http\Controllers\Controller;
use App\Models\User\Student;
use App\Models\User\StudentGuardian;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class GuardianController extends Controller
{
    public function index(Student $student): JsonResponse
    {
        $guardians = $student->guardians()->get();

        return response()->json([
            'success' => true,
            'data' => $guardians
        ]);
    }

    public function store(Request $request, Student $student): JsonResponse
    {
        $request->validate([
            'guardian_type' => 'required|in:father,mother,guardian',
            'full_name' => 'required|string|max:255',
            'occupation' => 'nullable|string|max:100',
            'annual_income' => 'nullable|numeric|min:0',
            'mobile_number' => 'nullable|string|max:15',
            'email' => 'nullable|email',
            'relation' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'is_primary_contact' => 'boolean',
        ]);

        // If setting as primary contact, remove primary from others
        if ($request->is_primary_contact) {
            $student->guardians()->update(['is_primary_contact' => false]);
        }

        $guardian = $student->guardians()->create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Guardian added successfully',
            'data' => $guardian
        ], 201);
    }

    public function show(Student $student, StudentGuardian $guardian): JsonResponse
    {
        // Ensure guardian belongs to student
        if ($guardian->student_id !== $student->id) {
            return response()->json([
                'success' => false,
                'message' => 'Guardian not found for this student'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $guardian
        ]);
    }

    public function update(Request $request, Student $student, StudentGuardian $guardian): JsonResponse
    {
        // Ensure guardian belongs to student
        if ($guardian->student_id !== $student->id) {
            return response()->json([
                'success' => false,
                'message' => 'Guardian not found for this student'
            ], 404);
        }

        $request->validate([
            'full_name' => 'required|string|max:255',
            'occupation' => 'nullable|string|max:100',
            'annual_income' => 'nullable|numeric|min:0',
            'mobile_number' => 'nullable|string|max:15',
            'email' => 'nullable|email',
            'relation' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'is_primary_contact' => 'boolean',
        ]);

        // If setting as primary contact, remove primary from others
        if ($request->is_primary_contact && !$guardian->is_primary_contact) {
            $student->guardians()->where('id', '!=', $guardian->id)
                ->update(['is_primary_contact' => false]);
        }

        $guardian->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Guardian updated successfully',
            'data' => $guardian
        ]);
    }

    public function destroy(Student $student, StudentGuardian $guardian): JsonResponse
    {
        // Ensure guardian belongs to student
        if ($guardian->student_id !== $student->id) {
            return response()->json([
                'success' => false,
                'message' => 'Guardian not found for this student'
            ], 404);
        }

        $guardian->delete();

        return response()->json([
            'success' => true,
            'message' => 'Guardian deleted successfully'
        ]);
    }
}