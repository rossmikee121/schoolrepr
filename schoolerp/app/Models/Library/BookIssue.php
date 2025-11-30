<?php

namespace App\Models\Library;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User\Student;

class BookIssue extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_id', 'student_id', 'issue_date', 'due_date',
        'return_date', 'fine_amount', 'fine_paid', 'status', 'remarks'
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'return_date' => 'date',
        'fine_amount' => 'decimal:2',
        'fine_paid' => 'boolean'
    ];

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'issued')
                    ->where('due_date', '<', now()->toDateString());
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'issued');
    }

    public function isOverdue()
    {
        return $this->status === 'issued' && $this->due_date < now()->toDateString();
    }
}