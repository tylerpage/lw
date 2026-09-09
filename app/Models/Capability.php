<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Capability extends Model
{
    protected $fillable = ['name', 'items', 'sort_order'];

    protected function casts(): array
    {
        return ['items' => 'array'];
    }
}
