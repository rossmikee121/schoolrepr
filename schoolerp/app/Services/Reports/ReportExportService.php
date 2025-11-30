<?php

namespace App\Services\Reports;

use App\Models\Reports\ReportExport;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportExportService
{
    protected ReportBuilderService $reportBuilder;

    public function __construct(ReportBuilderService $reportBuilder)
    {
        $this->reportBuilder = $reportBuilder;
    }

    public function exportToExcel(array $configuration, string $filename = null): string
    {
        $reportData = $this->reportBuilder->executeReport($configuration);
        $filename = $filename ?? 'report_' . now()->format('Y_m_d_H_i_s') . '.xlsx';
        
        $export = new class($reportData) implements \Maatwebsite\Excel\Concerns\FromArray, \Maatwebsite\Excel\Concerns\WithHeadings {
            protected $data;
            
            public function __construct($data)
            {
                $this->data = $data;
            }
            
            public function array(): array
            {
                return $this->data['data'];
            }
            
            public function headings(): array
            {
                if (empty($this->data['data'])) {
                    return [];
                }
                return array_keys($this->data['data'][0]);
            }
        };

        Excel::store($export, $filename, 'public');
        
        return Storage::disk('public')->path($filename);
    }

    public function exportToPdf(array $configuration, string $filename = null): string
    {
        $reportData = $this->reportBuilder->executeReport($configuration);
        $filename = $filename ?? 'report_' . now()->format('Y_m_d_H_i_s') . '.pdf';
        
        $pdf = Pdf::loadView('reports.pdf', [
            'data' => $reportData['data'],
            'configuration' => $reportData['configuration'],
            'generated_at' => now()->format('Y-m-d H:i:s')
        ]);
        
        $filePath = storage_path('app/public/' . $filename);
        $pdf->save($filePath);
        
        return $filePath;
    }

    public function exportToCsv(array $configuration, string $filename = null): string
    {
        $reportData = $this->reportBuilder->executeReport($configuration);
        $filename = $filename ?? 'report_' . now()->format('Y_m_d_H_i_s') . '.csv';
        $filePath = storage_path('app/public/' . $filename);
        
        $file = fopen($filePath, 'w');
        
        // Write headers
        if (!empty($reportData['data'])) {
            fputcsv($file, array_keys($reportData['data'][0]));
            
            // Write data
            foreach ($reportData['data'] as $row) {
                fputcsv($file, $row);
            }
        }
        
        fclose($file);
        
        return $filePath;
    }

    public function createExportJob(array $configuration, string $format, string $name, int $userId): ReportExport
    {
        return ReportExport::create([
            'name' => $name,
            'format' => $format,
            'status' => 'pending',
            'configuration' => $configuration,
            'user_id' => $userId
        ]);
    }

    public function processExportJob(ReportExport $export): void
    {
        try {
            $export->update(['status' => 'processing']);
            
            $filePath = match ($export->format) {
                'excel' => $this->exportToExcel($export->configuration, $export->name . '.xlsx'),
                'pdf' => $this->exportToPdf($export->configuration, $export->name . '.pdf'),
                'csv' => $this->exportToCsv($export->configuration, $export->name . '.csv'),
                default => throw new \InvalidArgumentException("Unsupported format: {$export->format}")
            };
            
            $export->markAsCompleted($filePath);
            
        } catch (\Exception $e) {
            $export->markAsFailed($e->getMessage());
        }
    }
}