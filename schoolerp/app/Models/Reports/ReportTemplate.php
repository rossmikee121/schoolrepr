<?php

namespace App\Models\Reports;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class ReportTemplate extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'description',
        'category',
        'configuration',
        'created_by',
        'is_public',
        'is_active'
    ];

    protected $casts = [
        'configuration' => 'array',
        'is_public' => 'boolean',
        'is_active' => 'boolean'
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }
}