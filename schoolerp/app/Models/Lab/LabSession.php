<?php

namespace App\Models\Lab;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Academic\Division;
use App\Models\User;

class LabSession extends Model
{
    use HasFactory;

    protected static function newFactory()
    {
        return \Database\Factories\LabSessionFactory::new();
    }

    protected $fillable = [
        'lab_id', 'division_id', 'subject_name', 'batch_number', 'max_students',
        'session_date', 'start_time', 'end_time', 'instructor_id', 'status'
    ];

    protected $casts = [
        'batch_number' => 'integer',
        'max_students' => 'integer',
        'session_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
    ];

    public function lab(): BelongsTo
    {
        return $this->belongsTo(Lab::class);
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function batches(): HasMany
    {
        return $this->hasMany(LabBatch::class);
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }
}