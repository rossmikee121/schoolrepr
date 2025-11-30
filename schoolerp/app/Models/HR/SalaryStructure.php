<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SalaryStructure extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'designation', 'basic_salary', 'hra', 'da',
        'other_allowances', 'pf_deduction', 'tax_deduction',
        'other_deductions', 'is_active'
    ];

    protected $casts = [
        'basic_salary' => 'decimal:2',
        'hra' => 'decimal:2',
        'da' => 'decimal:2',
        'other_allowances' => 'decimal:2',
        'pf_deduction' => 'decimal:2',
        'tax_deduction' => 'decimal:2',
        'other_deductions' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    public function staffSalaries()
    {
        return $this->hasMany(StaffSalary::class);
    }

    public function getTotalAllowancesAttribute()
    {
        return $this->hra + $this->da + $this->other_allowances;
    }

    public function getTotalDeductionsAttribute()
    {
        return $this->pf_deduction + $this->tax_deduction + $this->other_deductions;
    }

    public function getNetSalaryAttribute()
    {
        return $this->basic_salary + $this->total_allowances - $this->total_deductions;
    }
}