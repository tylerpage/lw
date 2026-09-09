<?php

namespace Tests\Unit;

use App\Support\MediaLibraryMimeTypes;
use Tests\TestCase;

class MediaLibraryMimeTypesTest extends TestCase
{
    public function test_it_accepts_configured_image_and_document_types(): void
    {
        $this->assertTrue(MediaLibraryMimeTypes::accepts('image/jpeg'));
        $this->assertTrue(MediaLibraryMimeTypes::accepts('application/pdf'));
        $this->assertTrue(MediaLibraryMimeTypes::accepts(null, 'brief.pdf'));
        $this->assertFalse(MediaLibraryMimeTypes::accepts('application/zip'));
    }

    public function test_it_identifies_vision_attachments(): void
    {
        $this->assertTrue(MediaLibraryMimeTypes::isVisionAttachment('image/png'));
        $this->assertFalse(MediaLibraryMimeTypes::isVisionAttachment('application/pdf'));
    }
}
