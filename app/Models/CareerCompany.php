<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CareerCompany extends Model
{
    protected $fillable = ['name', 'location', 'sort_order'];

    public function roles(): HasMany
    {
        return $this->hasMany(CareerRole::class)->orderByDesc('started_at');
    }
}
