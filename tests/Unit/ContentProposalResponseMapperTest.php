<?php

namespace Tests\Unit;

use App\ContentAssistant\Support\ContentProposalResponseMapper;
use PHPUnit\Framework\TestCase;

class ContentProposalResponseMapperTest extends TestCase
{
    public function test_it_normalizes_structured_response_into_proposal_data(): void
    {
        $data = (new ContentProposalResponseMapper)->toProposalData([
            'summary' => 'Update hero copy',
            'assistant_message' => 'I updated the hero subheadline.',
            'operations' => [[
                'op' => 'replace_block_fields',
                'block_index' => 0,
                'fields' => ['subheadline' => 'New copy'],
                'preserve' => ['primary_cta_label'],
                'field' => null,
            ]],
            'warnings' => [' Keep CTAs unchanged. '],
            'unverified_claims' => [],
            'sources' => [['id' => 1, 'title' => 'Approved bio baseline', 'excerpt' => 'Bio excerpt']],
        ]);

        $this->assertSame('Update hero copy', $data->summary);
        $this->assertSame('I updated the hero subheadline.', $data->assistantMessage);
        $this->assertSame([
            'op' => 'replace_block_fields',
            'block_index' => 0,
            'fields' => ['subheadline' => 'New copy'],
            'preserve' => ['primary_cta_label'],
        ], $data->operations[0]);
        $this->assertSame(['Keep CTAs unchanged.'], $data->warnings);
    }
}
