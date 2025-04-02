<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryTransaction extends Model
{
    protected $fillable = [
        'medication_id',
        'quantity',
        'transaction_type',
        'notes',
        'performed_by',
    ];
    protected $casts = [
        'quantity' => 'integer',
        'transaction_type' => 'string',
        'notes' => 'string',
        'performed_by' => 'integer',
    ];
    public function medication() : BelongsTo
    {
        return $this->belongsTo(Medication::class);
    }
    public function performedBy() : BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }


    // public function scopeInward($query)
    // {
    //     return $query->where('transaction_type', 'Inward');
    // }
    // public function scopeOutward($query)
    // {
    //     return $query->where('transaction_type', 'Outward');
    // }
    // public function scopeLatest($query)
    // {
    //     return $query->orderBy('created_at', 'desc');
    // }
    // public function scopeOldest($query)
    // {
    //     return $query->orderBy('created_at', 'asc');
    // }
    // public function scopeToday($query)
    // {
    //     return $query->whereDate('created_at', now());
    // }
    // public function scopeThisWeek($query)
    // {
    //     return $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
    // }
    // public function scopeThisMonth($query)
    // {
    //     return $query->whereMonth('created_at', now()->month);
    // }
    // public function scopeThisYear($query)
    // {
    //     return $query->whereYear('created_at', now()->year);
    // }
    // public function scopeLastWeek($query)
    // {
    //     return $query->whereBetween('created_at', [now()->subWeek(), now()]);
    // }
    // public function scopeLastMonth($query)
    // {
    //     return $query->whereBetween('created_at', [now()->subMonth(), now()]);
    // }
    // public function scopeLastYear($query)
    // {
    //     return $query->whereBetween('created_at', [now()->subYear(), now()]);
    // }
    // public function scopeBetweenDates($query, $startDate, $endDate)
    // {
    //     return $query->whereBetween('created_at', [$startDate, $endDate]);
    // }
    // public function scopeSearch($query, $search)
    // {
    //     return $query->where(function ($q) use ($search) {
    //         $q->where('transaction_type', 'like', "%{$search}%")
    //             ->orWhereHas('medication', function ($q) use ($search) {
    //                 $q->where('name', 'like', "%{$search}%");
    //             });
    //     });
    // }

}
