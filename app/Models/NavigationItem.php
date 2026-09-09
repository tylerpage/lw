<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NavigationItem extends Model
{
    protected $fillable = [
        'navigation_menu_id', 'parent_id', 'page_id', 'label', 'url', 'sort_order', 'open_in_new_tab',
    ];

    protected function casts(): array
    {
        return ['open_in_new_tab' => 'boolean'];
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(NavigationMenu::class, 'navigation_menu_id');
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order');
    }

    public function resolvedUrl(): string
    {
        if ($this->page) {
            return url('/'.ltrim($this->page->slug === 'home' ? '' : $this->page->slug, '/'));
        }

        return $this->url ?? '#';
    }
}
