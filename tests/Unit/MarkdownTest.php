<?php

namespace Tests\Unit;

use App\Support\Markdown;
use PHPUnit\Framework\TestCase;

class MarkdownTest extends TestCase
{
    public function test_it_renders_basic_markdown(): void
    {
        $html = Markdown::render("## Heading\n\n**Bold** and a [link](https://example.com).");

        $this->assertStringContainsString('<h2>Heading</h2>', $html);
        $this->assertStringContainsString('<strong>Bold</strong>', $html);
        $this->assertStringContainsString('href="https://example.com"', $html);
    }

    public function test_it_strips_raw_html(): void
    {
        $html = Markdown::render("Safe **text** with <script>alert(1)</script> inline.");

        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringContainsString('<strong>text</strong>', $html);
    }

    public function test_blank_content_renders_empty_string(): void
    {
        $this->assertSame('', Markdown::render(null));
        $this->assertSame('', Markdown::render('   '));
    }
}
