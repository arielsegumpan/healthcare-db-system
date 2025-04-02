<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrescriptionMedication extends Model
{
    protected $fillable = [
        'prescription_id',
        'medication_id',
        'quantity',
        'dosage',
        'frequency',
        'duration',
        'instructions'
    ];

    public function prescription(): BelongsTo
    {
        return $this->belongsTo(Prescription::class);
    }

    public function medication(): BelongsTo
    {
        return $this->belongsTo(Medication::class);
    }
}
