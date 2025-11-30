<?php

namespace App\Models\Fee;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeeHead extends Model
{
    use HasFactory;

    protected static function newFactory()
    {
        return \Database\Factories\FeeHeadFactory::new();
    }

    protected $fillable = ['name', 'code', 'description', 'is_refundable', 'is_active'];

    protected $casts = [
        'is_refundable' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function feeStructures(): HasMany
    {
        return $this->hasMany(FeeStructure::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}