<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VitalSign extends Model
{
    use HasFactory;

    protected $fillable = [
        'record_id',
        'temperature',
        'blood_pressure',
        'pulse_rate',
        'respiratory_rate',
        'height',
        'weight'
    ];

    public function medicalRecord() : BelongsTo
    {
        return $this->belongsTo(MedicalRecord::class, 'record_id', 'id');
    }
}
