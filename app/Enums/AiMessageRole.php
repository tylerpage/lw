<?php

namespace App\Enums;

enum AiMessageRole: string
{
    case User = 'user';
    case Assistant = 'assistant';
    case System = 'system';

    public function label(): string
    {
        return match ($this) {
            self::User => 'User',
            self::Assistant => 'Assistant',
            self::System => 'System',
        };
    }
}
