<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MedicalStaff extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'staff_type',
        'specialization',
        'license_number',
        'qualification',
        'from','to',
        'experience',
        'availability',
    ];

    protected $casts = [
        'availability' => 'array',
        'from' => 'date',
        'to' => 'date',
    ];

    public function user() : BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function appointments() : HasMany
    {
        return $this->hasMany(Appointment::class, 'staff_id', 'id');
    }

    public function medicalRecords(): HasMany
    {
        return $this->hasMany(MedicalRecord::class, 'staff_id', 'id');
    }

}
