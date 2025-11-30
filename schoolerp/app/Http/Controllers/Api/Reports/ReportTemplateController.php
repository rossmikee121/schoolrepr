<?php

namespace App\Http\Controllers\Api\Reports;

use App\Http\Controllers\Controller;
use App\Models\Reports\ReportTemplate;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ReportTemplateController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = ReportTemplate::with('creator:id,name')
            ->active();

        if ($request->category) {
            $query->byCategory($request->category);
        }

        if ($request->public_only) {
            $query->public();
        } else {
            // Show public templates and user's own templates
            $query->where(function ($q) {
                $q->where('is_public', true)
                  ->orWhere('created_by', auth()->id());
            });
        }

        $templates = $query->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => $templates
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'string|max:500',
            'category' => 'required|in:student,fee,academic,administrative',
            'configuration' => 'required|array',
            'configuration.base_model' => 'required|string',
            'is_public' => 'boolean'
        ]);

        $template = ReportTemplate::create([
            'name' => $request->name,
            'description' => $request->description,
            'category' => $request->category,
            'configuration' => $request->configuration,
            'created_by' => auth()->id(),
            'is_public' => $request->is_public ?? false
        ]);

        return response()->json([
            'success' => true,
            'data' => $template->load('creator:id,name'),
            'message' => 'Report template created successfully'
        ], 201);
    }

    public function show(ReportTemplate $template): JsonResponse
    {
        // Check if user can access this template
        if (!$template->is_public && $template->created_by !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $template->load('creator:id,name')
        ]);
    }

    public function update(Request $request, ReportTemplate $template): JsonResponse
    {
        // Only creator can update
        if ($template->created_by !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied'
            ], 403);
        }

        $request->validate([
            'name' => 'string|max:255',
            'description' => 'string|max:500',
            'category' => 'in:student,fee,academic,administrative',
            'configuration' => 'array',
            'configuration.base_model' => 'required_with:configuration|string',
            'is_public' => 'boolean'
        ]);

        $template->update($request->only([
            'name', 'description', 'category', 'configuration', 'is_public'
        ]));

        return response()->json([
            'success' => true,
            'data' => $template->fresh()->load('creator:id,name'),
            'message' => 'Report template updated successfully'
        ]);
    }

    public function destroy(ReportTemplate $template): JsonResponse
    {
        // Only creator can delete
        if ($template->created_by !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied'
            ], 403);
        }

        $template->delete();

        return response()->json([
            'success' => true,
            'message' => 'Report template deleted successfully'
        ]);
    }

    public function getByCategory(string $category): JsonResponse
    {
        $templates = ReportTemplate::with('creator:id,name')
            ->active()
            ->byCategory($category)
            ->where(function ($q) {
                $q->where('is_public', true)
                  ->orWhere('created_by', auth()->id());
            })
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $templates
        ]);
    }
}