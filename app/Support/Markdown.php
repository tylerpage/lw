<?php

namespace App\Support;

use Illuminate\Support\Str;

class Markdown
{
    public static function render(?string $content): string
    {
        if ($content === null || trim($content) === '') {
            return '';
        }

        return Str::markdown($content, [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);
    }
}
