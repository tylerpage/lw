<?php

namespace Database\Seeders;

use App\Models\ApprovedSource;
use App\Models\AssistantProfile;
use App\Models\User;
use Illuminate\Database\Seeder;

class ContentAssistantSeeder extends Seeder
{
    public function run(): void
    {
        AssistantProfile::query()->updateOrCreate(
            ['name' => 'Lindsey Wegmann Voice', 'version' => 1],
            [
                'instructions' => <<<'TEXT'
Write in a strategic, plainspoken, first-person voice for Lindsey Wegmann.
Be warm, direct, and useful. Prefer concrete language over buzzwords.
Taylor Swift and golden-retriever references are optional, removable, and never required.
Do not invent metrics, clients, testimonials, awards, dates, or credentials.
Never include copyrighted lyrics or imply celebrity endorsement.
TEXT,
                'is_active' => true,
            ]
        );

        $admin = User::query()->where('email', 'admin@example.com')->first();

        ApprovedSource::query()->updateOrCreate(
            ['title' => 'Portfolio requirements baseline'],
            [
                'source_type' => 'brand_guide',
                'content' => 'Approved positioning: Lindsey connects business goals, marketing, operations, and engineering for ecommerce strategy.',
                'approval_status' => 'approved',
                'approved_by' => $admin?->id,
                'approved_at' => now(),
                'applicable_areas' => ['all'],
            ]
        );

        ApprovedSource::query()->updateOrCreate(
            ['title' => 'Approved bio baseline'],
            [
                'source_type' => 'approved_bio',
                'content' => 'Senior digital strategist with ecommerce and retail operations experience across strategy, marketing, and project leadership.',
                'approval_status' => 'approved',
                'approved_by' => $admin?->id,
                'approved_at' => now(),
                'applicable_areas' => ['page', 'post', 'project'],
            ]
        );
    }
}
