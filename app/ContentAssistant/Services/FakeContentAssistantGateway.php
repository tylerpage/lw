<?php

namespace App\ContentAssistant\Services;

use App\ContentAssistant\Contracts\ContentAssistantGateway;
use App\ContentAssistant\DTO\ContentAssistantRequest;
use App\ContentAssistant\DTO\ContentProposalData;
use App\Models\Page;

class FakeContentAssistantGateway implements ContentAssistantGateway
{
    public function propose(ContentAssistantRequest $request): ContentProposalData
    {
        $message = strtolower($request->latestUserMessage);
        $context = $request->context;
        $attachments = $request->latestAttachments;

        if ($attachments !== [] && $this->mentionsHero($message) === false && $this->mentionsSeo($message) === false) {
            $urls = collect($attachments)->pluck('url')->implode(', ');

            return new ContentProposalData(
                summary: 'Reference image(s) received for content review.',
                operations: [],
                assistantMessage: "I received {$this->attachmentCountLabel(count($attachments))} at: {$urls}. Describe which section should change—for example, “Use this image for the homepage hero and rewrite the subheadline.”",
            );
        }

        if ($this->isUnsupportedCodeRequest($message)) {
            return new ContentProposalData(
                summary: 'This request requires a developer change.',
                operations: [],
                warnings: ['This requires a developer change in Cursor. The assistant cannot modify code, layouts, or block types.'],
                assistantMessage: 'I can help with content inside existing pages, posts, and case studies, but layout or code changes need to be done in Cursor.',
            );
        }

        if ($request->target instanceof Page && $this->mentionsHero($message)) {
            return $this->heroProposal($request, $context, $message);
        }

        if ($this->mentionsSeo($message)) {
            return $this->seoProposal($context, $message);
        }

        return new ContentProposalData(
            summary: 'No structured change generated for this request.',
            operations: [],
            assistantMessage: 'Tell me which page section to update—for example, "Make the homepage hero subheadline less formal while keeping both CTAs."',
        );
    }

    private function heroProposal(ContentAssistantRequest $request, array $context, string $message): ContentProposalData
    {
        $blocks = $context['blocks'] ?? [];
        $heroIndex = collect($blocks)->search(fn ($block) => ($block['type'] ?? null) === 'hero');

        if ($heroIndex === false) {
            return new ContentProposalData(
                summary: 'No hero block found on this page.',
                operations: [],
                assistantMessage: 'This page does not have a hero block to update.',
            );
        }

        $hero = $blocks[$heroIndex];
        $fields = [];
        $preserve = [];

        if (str_contains($message, 'cta')) {
            $preserve = array_merge($preserve, [
                'primary_cta_label', 'primary_cta_url', 'secondary_cta_label', 'secondary_cta_url',
            ]);
        }

        if (str_contains($message, 'formal') || str_contains($message, 'plainspoken') || str_contains($message, 'rewrite')) {
            $fields['subheadline'] = 'I connect marketing, operations, and engineering so ecommerce teams can move from strategy to execution without losing the plot.';
        }

        if (str_contains($message, 'marketing') && str_contains($message, 'engineering')) {
            $fields['headline'] = 'Ecommerce strategy that connects marketing and engineering.';
        }

        $attachment = $this->firstAttachment($request);

        if ($attachment && $this->mentionsImageUse($message)) {
            $fields['image'] = $attachment['public_path'] ?? $attachment['path'];
            $fields['image_alt'] = $attachment['original_name'] ?? 'Reference image';
        }

        if ($fields === []) {
            $fields['subheadline'] = 'I connect business goals, marketing, operations, and engineering into plans teams can actually execute.';
        }

        $operation = [
            'op' => 'replace_block_fields',
            'block_index' => $heroIndex,
            'block_id' => $hero['anchor_id'] ?? null,
            'fields' => $fields,
        ];

        if ($preserve !== []) {
            $operation['preserve'] = array_values(array_unique($preserve));
        }

        return new ContentProposalData(
            summary: 'Update the homepage hero copy while preserving existing CTAs.',
            operations: [$operation],
            assistantMessage: 'I drafted a hero copy update. Review the diff, revise if needed, then save as a draft to preview on the live templates.',
        );
    }

    private function seoProposal(array $context, string $message): ContentProposalData
    {
        $title = $context['seo']['title'] ?? $context['title'] ?? 'Page';
        $description = $context['seo']['description'] ?? '';

        return new ContentProposalData(
            summary: 'Draft SEO metadata improvements.',
            operations: [[
                'op' => 'replace_field',
                'field' => 'seo_title',
                'value' => str($title)->replace('[DRAFT] ', '')->trim()->toString(),
            ], [
                'op' => 'replace_field',
                'field' => 'seo_description',
                'value' => $description !== ''
                    ? str($description)->replace('[DRAFT] ', '')->trim()->toString()
                    : 'Senior digital strategist helping ecommerce teams connect marketing, operations, and engineering.',
            ]],
            assistantMessage: 'I drafted cleaner SEO title and description values. Validation will check length limits before you save a draft.',
        );
    }

    private function mentionsHero(string $message): bool
    {
        return str_contains($message, 'hero') || str_contains($message, 'homepage') || str_contains($message, 'headline');
    }

    private function mentionsSeo(string $message): bool
    {
        return str_contains($message, 'seo') || str_contains($message, 'meta description') || str_contains($message, 'title tag');
    }

    private function isUnsupportedCodeRequest(string $message): bool
    {
        foreach (['css', 'javascript', 'blade', 'php', 'layout', 'component', 'tailwind', 'migration'] as $needle) {
            if (str_contains($message, $needle)) {
                return true;
            }
        }

        return false;
    }

    private function attachmentCountLabel(int $count): string
    {
        return $count === 1 ? '1 reference image' : "{$count} reference images";
    }

    /**
     * @return array<string, mixed>|null
     */
    private function firstAttachment(ContentAssistantRequest $request): ?array
    {
        return $request->latestAttachments[0] ?? null;
    }

    private function mentionsImageUse(string $message): bool
    {
        foreach (['image', 'photo', 'picture', 'attached', 'upload', 'hero image', 'use this'] as $needle) {
            if (str_contains($message, $needle)) {
                return true;
            }
        }

        return false;
    }
}
