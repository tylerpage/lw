<?php

namespace Tests\Feature;

use App\Filament\Forms\BlockStateAdapter;
use App\PageBlocks\BlockRegistry;
use App\Rules\ValidBlockArray;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class FilamentBlockBuilderTest extends TestCase
{
    use RefreshDatabase;

    public function test_block_state_adapter_round_trips_flat_blocks(): void
    {
        $flat = [
            [
                'type' => 'hero',
                'enabled' => true,
                'headline' => 'Hello',
                'primary_cta_label' => 'Work',
                'primary_cta_url' => '/work',
            ],
            [
                'type' => 'rich_text',
                'enabled' => true,
                'content' => 'Body copy',
            ],
        ];

        $builder = BlockStateAdapter::toBuilder($flat);
        $restored = BlockStateAdapter::fromBuilder($builder);

        $this->assertSame($flat, $restored);
    }

    public function test_valid_block_array_rule_accepts_valid_blocks(): void
    {
        $validator = Validator::make([
            'blocks' => [
                ['type' => 'hero', 'enabled' => true, 'headline' => 'Title'],
            ],
        ], [
            'blocks' => [new ValidBlockArray],
        ]);

        $this->assertTrue($validator->passes());
    }

    public function test_valid_block_array_rule_rejects_invalid_blocks(): void
    {
        $validator = Validator::make([
            'blocks' => [
                ['type' => 'hero', 'enabled' => true],
            ],
        ], [
            'blocks' => [new ValidBlockArray],
        ]);

        $this->assertFalse($validator->passes());
        $this->assertStringContainsString('headline is required', $validator->errors()->first('blocks'));
    }

    public function test_all_registered_block_types_have_catalog_samples(): void
    {
        $this->assertCount(18, BlockRegistry::all());
    }
}
