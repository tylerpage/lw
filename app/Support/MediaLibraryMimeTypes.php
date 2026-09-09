<?php

namespace App\Support;

class MediaLibraryMimeTypes
{
    /**
     * @return array<int, string>
     */
    public static function allowed(): array
    {
        return config('content-assistant.media_library.allowed_mime_types', []);
    }

    /**
     * @return array<int, string>
     */
    public static function allowedExtensions(): array
    {
        return config('content-assistant.media_library.allowed_extensions', []);
    }

    /**
     * @return array<int, string>
     */
    public static function forFileUpload(): array
    {
        return self::allowed();
    }

    public static function accepts(?string $mimeType, ?string $filename = null): bool
    {
        if ($mimeType && in_array($mimeType, self::allowed(), true)) {
            return true;
        }

        if ($filename) {
            $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

            return $extension !== '' && in_array($extension, self::allowedExtensions(), true);
        }

        return false;
    }

    public static function isImage(?string $mimeType): bool
    {
        return is_string($mimeType) && str_starts_with($mimeType, 'image/');
    }

    public static function isVisionAttachment(?string $mimeType): bool
    {
        return self::isImage($mimeType);
    }

    public static function label(?string $mimeType, ?string $filename = null): string
    {
        if (self::isImage($mimeType)) {
            return 'Image';
        }

        if ($mimeType === 'application/pdf' || str_ends_with(strtolower((string) $filename), '.pdf')) {
            return 'PDF';
        }

        if (in_array($mimeType, ['text/plain', 'text/csv'], true)) {
            return 'Text';
        }

        if (in_array($mimeType, [
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ], true)) {
            return 'Document';
        }

        return 'File';
    }

    public static function helperText(): string
    {
        $extensions = implode(', ', self::allowedExtensions());

        return "Allowed types: {$extensions}. Images can be used in content blocks; PDFs and documents are reference files for the AI.";
    }
}
