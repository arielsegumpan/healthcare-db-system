<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MedicalRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'medical_record_num',
        'student_id',
        'staff_id',
        'appointment_id',
        'record_date',
        'diagnosis',
        'symptoms',
        'notes',
        'treatment',
        'follow_up_date'
    ];

    protected $casts = [
        'record_date' => 'datetime',
        'follow_up_date' => 'date',
        'vital_signs' => 'array',
    ];

    public function vitalSign() : HasOne
    {
        return $this->hasOne(VitalSign::class,'record_id', 'id');
    }

    public function student() : BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function staff() : BelongsTo
    {
        return $this->belongsTo(MedicalStaff::class, 'staff_id', 'id');
    }

    public function appointment() : BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class, 'record_id', 'id');
    }
}
