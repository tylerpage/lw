<?php

namespace App\ContentAssistant\Support;

use App\ContentAssistant\DTO\ContentAssistantRequest;
use App\PageBlocks\BlockRegistry;

class ContentProposalPromptBuilder
{
    public function buildInstructions(ContentAssistantRequest $request): string
    {
        $placeholders = implode(', ', config('content-assistant.internal_placeholders', []));
        $blockTypes = implode(', ', array_keys(BlockRegistry::all()));
        $targetType = $request->targetType->value;

        $voice = trim($request->assistantInstructions ?? 'Write in a clear, professional first-person voice.');

        return <<<TEXT
You are a content operations assistant for a Laravel portfolio CMS. You propose structured CMS changes only — never code, layout, CSS, JavaScript, Blade, migrations, or new block types.

Voice and style:
{$voice}

Hard boundaries:
- Content changes only inside existing {$targetType} records.
- Never invent clients, metrics, testimonials, awards, dates, roles, or credentials without approved sources.
- Use placeholders ({$placeholders}) when factual support is missing.
- Content fields support Markdown (headings, bold, lists, links). Do not include raw HTML tags or scripts.
- If the user asks for code, layout, styling, or infrastructure changes, return an empty operations array and explain the limitation in assistant_message and warnings.
- Do not auto-publish. Proposals are reviewed before saving as drafts.

Allowed operation types:
- replace_field: change a top-level field (field + value)
- replace_block_fields: change fields on an existing block (block_index or block_id, fields object, optional preserve array of field names to keep)
- insert_block, remove_block, move_block: only when clearly requested and block payload validates

Allowed top-level fields for {$targetType}:
- page: title, nav_label, seo_title, seo_description, og_title, og_description, canonical_url
- post: title, excerpt, seo_title, seo_description, og_title, og_description, canonical_url
- project: title, card_summary, seo_title, seo_description, og_title, og_description, canonical_url, role, client_display_name

Available block types: {$blockTypes}

When updating hero blocks, preserve CTA labels and URLs unless the user asks to change them. Use preserve for CTA fields when rewriting copy.

When the user attaches images, use the provided public_path values (e.g. storage/content-assistant/...) for hero or image block fields when appropriate.

Return JSON matching the schema. Keep assistant_message conversational and explain what you changed or why operations are empty.
TEXT;
    }

    public function buildPrompt(ContentAssistantRequest $request): string
    {
        $sections = [
            '## Current content context',
            json_encode($request->context, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ];

        if ($request->approvedSources !== []) {
            $sections[] = '## Approved sources (use for factual claims only)';
            $sections[] = json_encode($request->approvedSources, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        if ($request->latestAttachments !== []) {
            $sections[] = '## Attached reference images';
            $sections[] = json_encode($request->latestAttachments, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        $sections[] = '## Latest user request';
        $sections[] = $request->latestUserMessage;

        return implode("\n\n", $sections);
    }
}
