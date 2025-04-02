<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Prescription extends Model
{
    use HasFactory;

    protected $fillable = [
        'record_id',
        'created_by',
        'prescription_date',
        'notes'
    ];

    protected $casts = [
        'prescription_date' => 'datetime',
    ];


    public function medicalRecord() : BelongsTo
    {
        return $this->belongsTo(MedicalRecord::class, 'record_id');
    }

    public function createdBy() : BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    // public function medications() : BelongsToMany
    // {
    //     return $this->belongsToMany(Medication::class, 'prescription_medications')
    //         ->withPivot(['dosage', 'frequency', 'duration', 'instructions'])
    //         ->withTimestamps();
    // }

    public function prescriptionMedications(): HasMany
    {
        return $this->hasMany(PrescriptionMedication::class);
    }
}
