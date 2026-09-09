# Implementation plan

## Stack

| Layer | Choice |
|-------|--------|
| Runtime | PHP 8.3, Laravel 13 |
| Database | SQLite (local), PostgreSQL (production) |
| Public UI | Blade, Livewire 4, Alpine.js, Tailwind CSS 4 |
| Admin | Filament 5 |
| Auth/Roles | spatie/laravel-permission |
| Media | spatie/laravel-medialibrary |
| Audit | spatie/laravel-activitylog |
| Tests | PHPUnit |

## Routes

| Method | Path | Handler |
|--------|------|---------|
| GET | `/` | Home (page slug `home`) |
| GET | `/about`, `/privacy` | PageController |
| GET | `/work`, `/work/{slug}` | WorkController |
| GET | `/insights`, `/insights/{slug}` | InsightsController |
| GET | `/contact` | ContactController + Livewire form |
| GET | `/resume` | ResumeController (tracked download) |
| GET | `/search` | SearchController |
| GET | `/preview/{type}/{id}` | PreviewController (signed) |
| GET | `/sitemap.xml` | SitemapController |
| GET | `/feed.xml` | RssController |
| GET | `/robots.txt` | RobotsController |
| GET | `/admin` | Filament panel |

## Data model

Core entities: `Page`, `PageRevision`, `Post`, `PostRevision`, `Author`, `Category`, `Tag`, `Project`, `ProjectMetric`, `Discipline`, `Industry`, `Testimonial`, `NavigationMenu`, `NavigationItem`, `Redirect`, `ContactSubmission`, `SiteSetting`, `CareerCompany`, `CareerRole`, `Capability`, plus Spatie `media` and `roles/permissions`.

Publish status enum: `draft`, `scheduled`, `published`, `archived`.

## Page builder blocks

Hero, RichText, ImageText, FullWidthImage, CtaBanner, CardGrid, CapabilitiesGrid, FeaturedProjects, FeaturedPosts, Testimonials, Stats, Timeline, LogoStrip, Faq, Quote, Embed, Spacer, Personality — each validated JSON block mapped to a Blade component.

## Delivery phases

1. **Foundation** — Laravel, Filament, roles, design tokens, layout ✅
2. **CMS** — Pages, blog, projects, settings, navigation ✅
3. **Public site** — Templates, contact, SEO, RSS, redirects ✅
4. **Hardening** — Analytics, tests, CI, accessibility polish (ongoing)

## Known gaps / next steps

- Filament page builder uses JSON textarea; upgrade to structured repeater UI
- Media library integration on projects/posts (upload UI)
- MFA encouragement for admin users
- Scheduled publish command (`pages:publish-scheduled`, `posts:publish-scheduled`)
- Contact submission retention purge job
- Larastan in CI
- End-to-end browser tests (Playwright/Dusk)
- Lindsey content approval and production deploy

See [decisions.md](decisions.md) for reversible choices.
