<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;

class PreviewUrl
{
    public static function for(Model $model, int $expiresMinutes = 60): string
    {
        $type = match ($model->getTable()) {
            'pages' => 'page',
            'posts' => 'post',
            'projects' => 'project',
            default => throw new \InvalidArgumentException('Unsupported preview model.'),
        };

        return URL::temporarySignedRoute(
            'preview',
            now()->addMinutes($expiresMinutes),
            ['type' => $type, 'id' => $model->getKey()],
        );
    }
}
