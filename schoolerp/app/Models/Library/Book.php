<?php

namespace App\Models\Library;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'isbn', 'title', 'author', 'publisher', 'publication_year',
        'category', 'total_copies', 'available_copies', 'price',
        'description', 'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price' => 'decimal:2'
    ];

    public function issues()
    {
        return $this->hasMany(BookIssue::class);
    }

    public function activeIssues()
    {
        return $this->hasMany(BookIssue::class)->where('status', 'issued');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeAvailable($query)
    {
        return $query->where('available_copies', '>', 0);
    }
}