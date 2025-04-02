<?php

namespace App\Models;

use App\Observers\AppointmentObserver;
use App\Observers\UserObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

#[ObservedBy(AppointmentObserver::class)]
class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'appointment_num',
        'student_id',
        'appointment_date',
        'status',
        'reason',
        'priority',
        'notes',
        'created_by',
        'cancelled_reason'
    ];

    protected $casts = [
        'appointment_date' => 'datetime',
    ];

    public function student() : BelongsTo
    {
        return $this->belongsTo(Student::class);
    }


    public function creator() : BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function medicalRecord(): HasOne
    {
        return $this->hasOne(MedicalRecord::class);
    }
}
