<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Industry extends Model
{
    protected $fillable = ['name', 'slug'];

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_industry');
    }
}
