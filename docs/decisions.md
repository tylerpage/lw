# Implementation decisions

Recorded ambiguities and reversible choices made during initial scaffolding.

## PHP 8.3 instead of 8.4

The spec suggested PHP 8.4+, but this environment and `composer.json` target PHP 8.3. Laravel 13 and Filament 5 run on 8.3; upgrade to 8.4 when hosting supports it.

## PHPUnit instead of Pest

The spec allowed either framework. This project uses PHPUnit to match the Laravel 13 skeleton and keep CI simple.

## Custom sitemap instead of spatie/laravel-sitemap

A small `SitemapController` renders published, indexable URLs only. This avoids an extra dependency for a straightforward sitemap.

## Plausible as default analytics

`AnalyticsService` supports Plausible first and GTM/GA4 as an alternative via environment variables. Plausible aligns with the privacy-first preference in the spec.

## Experience on About page, not separate nav

Career timeline content lives on `/about` via the Timeline block and seeded `career_*` tables. No `/experience` route in primary navigation unless content length requires a split later.

## Published seed with draft copy labels

Core pages, demo posts, and demo projects are seeded as **published** so the site is browsable locally, but all copy is prefixed with `[DRAFT]` until Lindsey approves it for launch.

## In-site conversational content assistant (Phase 1–2)

Built a proposal-first content assistant for Filament using a fake provider by default (`CONTENT_ASSISTANT_DRIVER=fake`). The engine stores conversations/proposals, validates structured operations against existing page-builder schemas, records audit events, saves drafts only (never auto-publishes), and exposes safe Artisan inspection/proposal commands. Live provider integration, approval/publish confirmation tokens, and blog/case-study creation flows remain for later phases.

## Demo content is clearly fictional

Case studies use `[DRAFT] Demo Case Study` titles and `[DRAFT] Demo Client` names. Blog posts are labeled `[DRAFT] Demo Post`.

## Filament block builder + external AI workshop import

Pages, posts, and case studies are edited on dedicated Filament edit pages with a visual block builder instead of raw JSON textareas. Filament Builder stores `{type, data}` internally; `BlockStateAdapter` converts to/from the flat `{type, enabled, ...fields}` JSON used on the public site and validated by `BlockRegistry`.

Editors can also workshop copy in external ChatGPT sessions: **Copy AI context** exports a markdown brief (voice rules, block catalog, current record, import schema v1), and **Import content** accepts pasted JSON validated by the same registry and claim rules. Imports always save as draft and record a `content_import_applied` audit event. This complements the in-app Content Assistant; it does not replace it.

## AI assistant image attachments

The in-app Content Assistant accepts reference images on each message. Files upload to the `public` disk under `content-assistant/{conversation_id}/`, are stored on the message as URL + `public_path` metadata, and are passed to the assistant gateway in request context. Block proposals can reference uploaded images via the `storage/...` public path for hero and image blocks.

## AI SEO quick actions on content edit screens

Page, post, and case study edit/create forms include SEO tab quick buttons backed by `SeoGenerationService`. Suggestions are generated from the current title, excerpt/summary, and block content, then applied to the form only (not auto-saved). Modes: full SEO, meta only, Open Graph only, and improve existing.

## Public maintenance mode with IP bypass

Maintenance mode is toggled in Site Settings and shows a minimal public page (logo + site name only). Middleware is applied only to public routes in `routes/web.php`, so Filament admin and Livewire requests are unaffected. Additional bypass IPs are managed in Site Settings (one per line) with optional `MAINTENANCE_ALLOWLIST_IPS` env fallback.
