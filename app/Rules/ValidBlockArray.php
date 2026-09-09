<?php

namespace App\Rules;

use App\PageBlocks\BlockRegistry;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidBlockArray implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_array($value)) {
            $fail('Content blocks must be an array.');

            return;
        }

        foreach ($value as $index => $block) {
            if (! is_array($block)) {
                $fail("Block {$index}: must be an object.");

                continue;
            }

            $type = $block['type'] ?? 'unknown';

            foreach (BlockRegistry::validate($block) as $error) {
                $fail("Block {$index} ({$type}): {$error}");
            }
        }
    }
}
