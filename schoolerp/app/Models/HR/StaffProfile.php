<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;
use App\Models\Academic\Department;

class StaffProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'employee_id', 'first_name', 'last_name', 'phone',
        'emergency_contact', 'date_of_birth', 'gender', 'address',
        'joining_date', 'designation', 'department_id', 'employment_type', 'status'
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'joining_date' => 'date'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function salaries()
    {
        return $this->hasMany(StaffSalary::class, 'staff_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }
}