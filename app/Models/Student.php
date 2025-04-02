<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'lrn_number',
        'first_name',
        'last_name',
        'gender',
        'grade_level',
        'section',
        'date_of_birth',
        'blood_group',
        'address',
        'parent_name',
        'parent_contact',
        'allergies'
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];


    public function user() : BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function appointments() : HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function medicalRecords() : HasMany
    {
        return $this->hasMany(MedicalRecord::class);
    }
}
