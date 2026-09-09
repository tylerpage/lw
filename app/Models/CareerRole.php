<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CareerRole extends Model
{
    protected $fillable = [
        'career_company_id', 'title', 'started_at', 'ended_at', 'summary', 'highlights', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'date',
            'ended_at' => 'date',
            'highlights' => 'array',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(CareerCompany::class, 'career_company_id');
    }

    public function dateRangeLabel(): string
    {
        $start = $this->started_at->format('F Y');
        $end = $this->ended_at ? $this->ended_at->format('F Y') : 'Present';

        return "{$start} – {$end}";
    }
}
