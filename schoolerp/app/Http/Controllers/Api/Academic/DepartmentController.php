<?php

namespace App\Http\Controllers\Api\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\Department;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DepartmentController extends Controller
{
    public function index(): JsonResponse
    {
        $departments = Department::active()
            ->with(['programs' => function($query) {
                $query->active();
            }])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $departments
        ]);
    }

    public function show(Department $department): JsonResponse
    {
        $department->load(['programs.academicYears', 'hod']);

        return response()->json([
            'success' => true,
            'data' => $department
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:departments',
            'code' => 'required|string|max:20|unique:departments',
            'description' => 'nullable|string',
            'hod_user_id' => 'nullable|exists:users,id',
        ]);

        $department = Department::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Department created successfully',
            'data' => $department
        ], 201);
    }

    public function update(Request $request, Department $department): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:departments,name,' . $department->id,
            'code' => 'required|string|max:20|unique:departments,code,' . $department->id,
            'description' => 'nullable|string',
            'hod_user_id' => 'nullable|exists:users,id',
            'is_active' => 'boolean',
        ]);

        $department->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Department updated successfully',
            'data' => $department
        ]);
    }
}