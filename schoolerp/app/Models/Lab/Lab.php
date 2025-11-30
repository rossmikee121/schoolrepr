<?php

namespace App\Models\Lab;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lab extends Model
{
    use HasFactory;

    protected static function newFactory()
    {
        return \Database\Factories\LabFactory::new();
    }

    protected $fillable = ['name', 'code', 'capacity', 'location', 'equipment', 'is_active'];

    protected $casts = [
        'capacity' => 'integer',
        'is_active' => 'boolean',
    ];

    public function sessions(): HasMany
    {
        return $this->hasMany(LabSession::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}