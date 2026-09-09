<?php

namespace App\ContentAssistant\Support;

use App\ContentAssistant\DTO\ContentProposalData;
use App\ContentAssistant\Services\MediaLibraryService;
use App\Models\Page;
use App\Models\Post;
use App\Models\Project;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ProposalAttachmentNormalizer
{
    /**
     * @param  array<int, array<string, mixed>>  $attachments
     */
    public function normalize(
        ContentProposalData $proposal,
        array $attachments,
        string $latestUserMessage,
        Model $target,
    ): ContentProposalData {
        $operations = $this->normalizeImagePaths($proposal->operations, $attachments);

        if ($attachments !== [] && $this->userRequestedImageUse($latestUserMessage)) {
            $operations = $this->ensureImageApplied($operations, $attachments, $target);
        }

        if ($operations === $proposal->operations) {
            return $proposal;
        }

        return new ContentProposalData(
            summary: $proposal->summary,
            operations: $operations,
            sources: $proposal->sources,
            warnings: $proposal->warnings,
            unverifiedClaims: $proposal->unverifiedClaims,
            assistantMessage: $proposal->assistantMessage,
        );
    }

    /**
     * @param  array<int, array<string, mixed>>  $operations
     * @param  array<int, array<string, mixed>>  $attachments
     * @return array<int, array<string, mixed>>
     */
    private function normalizeImagePaths(array $operations, array $attachments): array
    {
        return array_map(function (array $operation) use ($attachments): array {
            if (($operation['op'] ?? '') === 'replace_block_fields' && isset($operation['fields']['image'])) {
                $operation['fields']['image'] = $this->normalizeImageValue(
                    (string) $operation['fields']['image'],
                    $attachments,
                );
            }

            if (($operation['op'] ?? '') === 'insert_block' && isset($operation['block']['image'])) {
                $operation['block']['image'] = $this->normalizeImageValue(
                    (string) $operation['block']['image'],
                    $attachments,
                );
            }

            return $operation;
        }, $operations);
    }

    /**
     * @param  array<int, array<string, mixed>>  $attachments
     */
    private function normalizeImageValue(string $value, array $attachments): string
    {
        $value = trim($value);

        if ($value === '') {
            return $value;
        }

        foreach ($attachments as $attachment) {
            $path = (string) ($attachment['path'] ?? '');
            $publicPath = (string) ($attachment['public_path'] ?? ($path !== '' ? 'storage/'.$path : ''));
            $url = (string) ($attachment['url'] ?? '');

            if ($value === $publicPath || $value === $path || ($url !== '' && $value === $url)) {
                return $publicPath;
            }

            if ($path !== '' && str_contains($value, $path)) {
                return $publicPath;
            }
        }

        if (str_contains($value, '/storage/')) {
            return 'storage'.Str::after($value, '/storage');
        }

        $libraryPath = app(MediaLibraryService::class)->resolvePublicPath($value);

        return $libraryPath ?? $value;
    }

    /**
     * @param  array<int, array<string, mixed>>  $operations
     * @param  array<int, array<string, mixed>>  $attachments
     * @return array<int, array<string, mixed>>
     */
    private function ensureImageApplied(array $operations, array $attachments, Model $target): array
    {
        if ($this->operationsIncludeImage($operations)) {
            return $operations;
        }

        $heroIndex = $this->findHeroBlockIndex($target);

        if ($heroIndex === null) {
            return $operations;
        }

        $attachment = $attachments[0];
        $publicPath = (string) ($attachment['public_path'] ?? 'storage/'.($attachment['path'] ?? ''));

        if ($publicPath === 'storage/') {
            return $operations;
        }

        foreach ($operations as &$operation) {
            if (($operation['op'] ?? '') !== 'replace_block_fields') {
                continue;
            }

            if (($operation['block_index'] ?? null) !== $heroIndex) {
                continue;
            }

            $operation['fields']['image'] = $publicPath;
            $operation['fields']['image_alt'] = $operation['fields']['image_alt']
                ?? ($attachment['original_name'] ?? 'Reference image');

            return $operations;
        }
        unset($operation);

        $operations[] = [
            'op' => 'replace_block_fields',
            'block_index' => $heroIndex,
            'fields' => [
                'image' => $publicPath,
                'image_alt' => $attachment['original_name'] ?? 'Reference image',
            ],
            'preserve' => [
                'primary_cta_label',
                'primary_cta_url',
                'secondary_cta_label',
                'secondary_cta_url',
            ],
        ];

        return $operations;
    }

    /**
     * @param  array<int, array<string, mixed>>  $operations
     */
    private function operationsIncludeImage(array $operations): bool
    {
        foreach ($operations as $operation) {
            if (($operation['op'] ?? '') === 'replace_block_fields' && filled($operation['fields']['image'] ?? null)) {
                return true;
            }

            if (($operation['op'] ?? '') === 'insert_block' && filled($operation['block']['image'] ?? null)) {
                return true;
            }
        }

        return false;
    }

    private function userRequestedImageUse(string $message): bool
    {
        $message = strtolower($message);

        foreach ([
            'use this image',
            'use the image',
            'use that image',
            'use an image',
            'use the attached',
            'use this photo',
            'use this picture',
            'attached image',
            'hero image',
            'for the hero',
            'for the homepage hero',
            'set the image',
            'update the image',
            'replace the image',
            'use image',
        ] as $needle) {
            if (str_contains($message, $needle)) {
                return true;
            }
        }

        return str_contains($message, 'image') && (
            str_contains($message, 'use')
            || str_contains($message, 'attach')
            || str_contains($message, 'hero')
        );
    }

    private function findHeroBlockIndex(Model $target): ?int
    {
        $blocks = match ($target::class) {
            Page::class, Project::class => $target->blocks ?? [],
            Post::class => $target->body ?? [],
            default => [],
        };

        $index = collect($blocks)->search(fn (array $block): bool => ($block['type'] ?? null) === 'hero');

        return $index === false ? null : $index;
    }
}
