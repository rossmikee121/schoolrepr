<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StaffSalary extends Model
{
    use HasFactory;

    protected $fillable = [
        'staff_id', 'salary_structure_id', 'salary_year', 'salary_month',
        'basic_salary', 'total_allowances', 'total_deductions',
        'net_salary', 'payment_date', 'status'
    ];

    protected $casts = [
        'basic_salary' => 'decimal:2',
        'total_allowances' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'net_salary' => 'decimal:2',
        'payment_date' => 'date'
    ];

    public function staff()
    {
        return $this->belongsTo(StaffProfile::class, 'staff_id');
    }

    public function salaryStructure()
    {
        return $this->belongsTo(SalaryStructure::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function getMonthNameAttribute()
    {
        return date('F', mktime(0, 0, 0, $this->salary_month, 1));
    }
}