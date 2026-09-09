<?php

namespace App\Enums;

enum UserRole: string
{
    case SuperAdmin = 'super_admin';
    case Editor = 'editor';
    case Author = 'author';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Admin',
            self::Editor => 'Editor',
            self::Author => 'Author',
        };
    }
}
