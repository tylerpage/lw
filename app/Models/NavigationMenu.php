<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NavigationMenu extends Model
{
    protected $fillable = ['name', 'location'];

    public function items(): HasMany
    {
        return $this->hasMany(NavigationItem::class)->whereNull('parent_id')->orderBy('sort_order');
    }

    public function allItems(): HasMany
    {
        return $this->hasMany(NavigationItem::class)->orderBy('sort_order');
    }
}
