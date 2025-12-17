<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchoolAccount extends Model
{
    protected $fillable = [
        'school_id',
        'total_expected',
        'total_received',
    ];

    protected $casts = [
        'total_expected' => 'decimal:2',
        'total_received' => 'decimal:2',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function getBalanceAttribute()
    {
        return $this->total_expected - $this->total_received;
    }

    
}
