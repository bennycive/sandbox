<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentRequest extends Model
{
    protected $fillable = [

        'school_id',
        'school_code',
        'school_name',
        'control_number',
        'request_id',
        'student_id',
        'student_name',
        'amount_required',
        'amount_paid',
        'currency',
        'description',
        'status',
        
    ];

    protected $casts = [
        'amount_required' => 'decimal:2',
        'amount_paid' => 'decimal:2',
    ];

    /* ================= RELATIONS ================= */

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(PaymentLog::class);
    }

    /* ================= ACCESSORS ================= */

    public function getBalanceAttribute()
    {
        return $this->amount_required - $this->amount_paid;
    }

    /* ================= STATUS LOGIC ================= */

    public function refreshStatus(): void
    {
        if ($this->amount_paid <= 0) {
            $this->status = 'PENDING';
        } elseif ($this->amount_paid < $this->amount_required) {
            $this->status = 'PARTIAL';
        } else {
            $this->status = 'FULL';
        }

        $this->save();
    }


}

