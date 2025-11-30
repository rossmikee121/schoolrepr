<?php

namespace App\Models\Reports;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class ReportExport extends Model
{
    protected $fillable = [
        'name',
        'format',
        'status',
        'file_path',
        'configuration',
        'user_id',
        'completed_at',
        'error_message'
    ];

    protected $casts = [
        'configuration' => 'array',
        'completed_at' => 'datetime'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function markAsCompleted($filePath)
    {
        $this->update([
            'status' => 'completed',
            'file_path' => $filePath,
            'completed_at' => now()
        ]);
    }

    public function markAsFailed($errorMessage)
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage
        ]);
    }
}