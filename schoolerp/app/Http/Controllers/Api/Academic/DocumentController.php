<?php

namespace App\Http\Controllers\Api\Academic;

use App\Http\Controllers\Controller;
use App\Models\User\Student;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function uploadPhoto(Request $request, Student $student): JsonResponse
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg|max:2048', // 2MB max
        ]);

        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($student->photo_path) {
                Storage::disk('public')->delete($student->photo_path);
            }

            // Store new photo
            $path = $request->file('photo')->store('student-photos', 'public');
            
            $student->update(['photo_path' => $path]);

            return response()->json([
                'success' => true,
                'message' => 'Photo uploaded successfully',
                'data' => [
                    'photo_url' => Storage::url($path)
                ]
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No photo file provided'
        ], 400);
    }

    public function uploadSignature(Request $request, Student $student): JsonResponse
    {
        $request->validate([
            'signature' => 'required|image|mimes:jpeg,png,jpg|max:1024', // 1MB max
        ]);

        if ($request->hasFile('signature')) {
            // Delete old signature if exists
            if ($student->signature_path) {
                Storage::disk('public')->delete($student->signature_path);
            }

            // Store new signature
            $path = $request->file('signature')->store('student-signatures', 'public');
            
            $student->update(['signature_path' => $path]);

            return response()->json([
                'success' => true,
                'message' => 'Signature uploaded successfully',
                'data' => [
                    'signature_url' => Storage::url($path)
                ]
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No signature file provided'
        ], 400);
    }

    public function getDocuments(Student $student): JsonResponse
    {
        $documents = [
            'photo' => $student->photo_path ? Storage::url($student->photo_path) : null,
            'signature' => $student->signature_path ? Storage::url($student->signature_path) : null,
        ];

        return response()->json([
            'success' => true,
            'data' => $documents
        ]);
    }

    public function deletePhoto(Student $student): JsonResponse
    {
        if ($student->photo_path) {
            Storage::disk('public')->delete($student->photo_path);
            $student->update(['photo_path' => null]);

            return response()->json([
                'success' => true,
                'message' => 'Photo deleted successfully'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No photo to delete'
        ], 404);
    }

    public function deleteSignature(Student $student): JsonResponse
    {
        if ($student->signature_path) {
            Storage::disk('public')->delete($student->signature_path);
            $student->update(['signature_path' => null]);

            return response()->json([
                'success' => true,
                'message' => 'Signature deleted successfully'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No signature to delete'
        ], 404);
    }
}