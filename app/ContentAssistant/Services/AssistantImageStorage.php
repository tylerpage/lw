<?php

namespace App\ContentAssistant\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class AssistantImageStorage
{
    /**
     * @param  array<int, TemporaryUploadedFile|UploadedFile>  $files
     * @return array<int, array{path: string, url: string, original_name: string, mime_type: string, size: int}>
     */
    public function storeMany(array $files, int $conversationId): array
    {
        return collect($files)
            ->map(fn (TemporaryUploadedFile|UploadedFile $file): array => $this->store($file, $conversationId))
            ->values()
            ->all();
    }

    /**
     * @return array{path: string, url: string, original_name: string, mime_type: string, size: int}
     */
    public function store(TemporaryUploadedFile|UploadedFile $file, int $conversationId): array
    {
        $disk = config('content-assistant.attachments.disk', 'public');
        $directory = trim(config('content-assistant.attachments.directory', 'content-assistant'), '/');
        $extension = $file->getClientOriginalExtension() ?: $file->extension() ?: 'jpg';
        $filename = Str::uuid()->toString().'.'.strtolower($extension);
        $path = $file->storeAs("{$directory}/{$conversationId}", $filename, [
            'disk' => $disk,
            'visibility' => 'public',
        ]);

        return [
            'path' => $path,
            'url' => Storage::disk($disk)->url($path),
            'public_path' => 'storage/'.$path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType() ?? 'application/octet-stream',
            'size' => $file->getSize() ?? 0,
        ];
    }
}
