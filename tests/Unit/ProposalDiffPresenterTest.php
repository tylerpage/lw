<?php

namespace Tests\Unit;

use App\ContentAssistant\Support\ProposalDiffPresenter;
use PHPUnit\Framework\TestCase;

class ProposalDiffPresenterTest extends TestCase
{
    public function test_block_fields_present_only_changed_fields(): void
    {
        $presented = app(ProposalDiffPresenter::class)->present([
            [
                'type' => 'block_fields',
                'label' => 'Hero (section 1)',
                'before' => [
                    'type' => 'hero',
                    'enabled' => true,
                    'headline' => 'Old headline',
                    'subheadline' => 'Old subheadline',
                    'primary_cta_label' => 'Work',
                ],
                'after' => [
                    'type' => 'hero',
                    'enabled' => true,
                    'headline' => 'New headline',
                    'subheadline' => 'Old subheadline',
                    'primary_cta_label' => 'Work',
                ],
                'preserved' => ['primary_cta_label', 'primary_cta_url'],
            ],
        ]);

        $this->assertSame('Hero block', $presented[0]['title']);
        $this->assertCount(1, $presented[0]['changes']);
        $this->assertSame('Headline', $presented[0]['changes'][0]['field']);
        $this->assertSame('Old headline', $presented[0]['changes'][0]['before']);
        $this->assertSame('New headline', $presented[0]['changes'][0]['after']);
        $this->assertContains('Primary button label', $presented[0]['preserved']);
    }

    public function test_field_diff_uses_human_labels(): void
    {
        $presented = app(ProposalDiffPresenter::class)->present([
            [
                'type' => 'field',
                'label' => 'seo_title',
                'before' => 'Old title',
                'after' => 'New title',
            ],
        ]);

        $this->assertSame('SEO title', $presented[0]['title']);
    }
}
