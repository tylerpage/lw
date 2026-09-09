<?php

namespace App\Filament\Resources\Concerns;

use App\Support\ContentSlug;

trait InteractsWithContentSlug
{
    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return ContentSlug::ensure(
            $data,
            static::getResource()::getModel(),
            fallback: $this->contentSlugFallback(),
        );
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        return ContentSlug::ensure(
            $data,
            static::getResource()::getModel(),
            ignoreId: $this->getRecord()?->getKey(),
            fallback: $this->contentSlugFallback(),
        );
    }

    protected function contentSlugFallback(): string
    {
        return 'untitled';
    }
}
