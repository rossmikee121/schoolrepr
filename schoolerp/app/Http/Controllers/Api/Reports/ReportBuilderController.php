<?php

namespace App\Http\Controllers\Api\Reports;

use App\Http\Controllers\Controller;
use App\Services\Reports\ReportBuilderService;
use App\Services\Reports\ReportExportService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ReportBuilderController extends Controller
{
    protected ReportBuilderService $reportBuilder;
    protected ReportExportService $exportService;

    public function __construct(ReportBuilderService $reportBuilder, ReportExportService $exportService)
    {
        $this->reportBuilder = $reportBuilder;
        $this->exportService = $exportService;
    }

    public function getAvailableModels(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->reportBuilder->getAvailableModels()
        ]);
    }

    public function getAvailableColumns(Request $request): JsonResponse
    {
        $model = $request->query('model');
        
        return response()->json([
            'success' => true,
            'data' => $this->reportBuilder->getAvailableColumns($model)
        ]);
    }

    public function buildReport(Request $request): JsonResponse
    {
        $request->validate([
            'base_model' => 'required|string',
            'columns' => 'array',
            'columns.*.field' => 'required|string',
            'columns.*.alias' => 'string',
            'filters' => 'array',
            'filters.logic' => 'in:and,or',
            'filters.conditions' => 'array',
            'filters.conditions.*.column' => 'required|string',
            'filters.conditions.*.operator' => 'required|string',
            'filters.conditions.*.value' => 'required',
            'joins' => 'array',
            'order_by' => 'array',
            'order_by.*.column' => 'required|string',
            'order_by.*.direction' => 'in:asc,desc',
            'limit' => 'integer|min:1|max:10000'
        ]);

        try {
            $configuration = $request->all();
            $result = $this->reportBuilder->executeReport($configuration);

            return response()->json([
                'success' => true,
                'data' => $result['data'],
                'total' => $result['total'],
                'configuration' => $result['configuration']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to build report: ' . $e->getMessage()
            ], 400);
        }
    }

    public function exportReport(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'format' => 'required|in:excel,pdf,csv',
            'configuration' => 'required|array',
            'configuration.base_model' => 'required|string'
        ]);

        try {
            $export = $this->exportService->createExportJob(
                $request->configuration,
                $request->format,
                $request->name,
                auth()->id()
            );

            // Process export immediately for now (can be queued later)
            $this->exportService->processExportJob($export);

            return response()->json([
                'success' => true,
                'data' => [
                    'export_id' => $export->id,
                    'status' => $export->fresh()->status,
                    'file_path' => $export->fresh()->file_path
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to export report: ' . $e->getMessage()
            ], 400);
        }
    }

    public function getExportStatus(int $exportId): JsonResponse
    {
        $export = auth()->user()->reportExports()->findOrFail($exportId);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $export->id,
                'name' => $export->name,
                'format' => $export->format,
                'status' => $export->status,
                'file_path' => $export->file_path,
                'created_at' => $export->created_at,
                'completed_at' => $export->completed_at,
                'error_message' => $export->error_message
            ]
        ]);
    }

    public function downloadExport(int $exportId)
    {
        $export = auth()->user()->reportExports()->findOrFail($exportId);

        if ($export->status !== 'completed' || !$export->file_path) {
            return response()->json([
                'success' => false,
                'message' => 'Export not ready for download'
            ], 400);
        }

        if (!file_exists($export->file_path)) {
            return response()->json([
                'success' => false,
                'message' => 'Export file not found'
            ], 404);
        }

        return response()->download($export->file_path);
    }
}