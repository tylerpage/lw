<?php

namespace App\Support;

use App\Models\ContentProposal;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;

class PreviewUrl
{
    public static function for(Model $model, int $expiresMinutes = 60, ?int $revisionId = null): string
    {
        $type = self::typeForModel($model);

        $parameters = ['type' => $type, 'id' => $model->getKey()];

        if ($revisionId !== null) {
            $parameters['revision'] = $revisionId;
        }

        return URL::temporarySignedRoute(
            'preview',
            now()->addMinutes($expiresMinutes),
            $parameters,
        );
    }

    public static function forProposal(ContentProposal $proposal, int $expiresMinutes = 60): string
    {
        $target = $proposal->target();

        if (! $target) {
            throw new \InvalidArgumentException('Proposal target no longer exists.');
        }

        $parameters = [
            'type' => self::typeForModel($target),
            'id' => $target->getKey(),
            'proposal' => $proposal->getKey(),
        ];

        return URL::temporarySignedRoute(
            'preview',
            now()->addMinutes($expiresMinutes),
            $parameters,
        );
    }

    private static function typeForModel(Model $model): string
    {
        return match ($model->getTable()) {
            'pages' => 'page',
            'posts' => 'post',
            'projects' => 'project',
            default => throw new \InvalidArgumentException('Unsupported preview model.'),
        };
    }
}
