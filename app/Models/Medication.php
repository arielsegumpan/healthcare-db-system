<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Medication extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'generic_name',
        'brand_name',
        'med_img',
        'description',
        'dosage_form',
        'stock',
        'expiry_date',
        'manufacturer',
        'notes'
    ];

    protected $casts = [
        'expiry_date' => 'date',
    ];

    // public function prescriptions(): BelongsToMany
    // {
    //     return $this->belongsToMany(Prescription::class, 'prescription_medications')
    //         ->withPivot(['dosage', 'frequency', 'duration', 'instructions'])
    //         ->withTimestamps();
    // }

    public function prescriptionMedications(): HasMany
    {
        return $this->hasMany(PrescriptionMedication::class);
    }

    public function inventoryTransactions(): HasMany
    {
        return $this->hasMany(InventoryTransaction::class);
    }
}
