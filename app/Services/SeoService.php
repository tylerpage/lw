<?php

namespace App\Services;

use App\Models\Page;
use App\Models\Post;
use App\Models\Project;
use App\Models\SiteSetting;

class SeoService
{
    public function forPage(Page $page): array
    {
        return [
            'title' => $page->seoTitle(),
            'description' => $page->seoDescription(),
            'canonical' => $page->canonical_url ?: url($page->slug === 'home' ? '/' : '/'.$page->slug),
            'og_title' => $page->og_title ?: $page->seoTitle(),
            'og_description' => $page->og_description ?: $page->seoDescription(),
            'og_image' => $page->og_image ?: SiteSetting::get('default_og_image'),
            'robots' => $this->robotsDirective($page->isIndexable(), $page->isFollowable()),
        ];
    }

    public function forPost(Post $post): array
    {
        return [
            'title' => $post->seoTitle(),
            'description' => $post->seoDescription(),
            'canonical' => $post->canonical_url ?: url('/insights/'.$post->slug),
            'og_title' => $post->og_title ?: $post->seoTitle(),
            'og_description' => $post->og_description ?: $post->seoDescription(),
            'og_image' => $post->og_image ?: $post->hero_image ?: SiteSetting::get('default_og_image'),
            'robots' => $this->robotsDirective($post->isIndexable(), $post->isFollowable()),
        ];
    }

    public function forProject(Project $project): array
    {
        return [
            'title' => $project->seoTitle(),
            'description' => $project->seoDescription(),
            'canonical' => $project->canonical_url ?: url('/work/'.$project->slug),
            'og_title' => $project->og_title ?: $project->seoTitle(),
            'og_description' => $project->og_description ?: $project->seoDescription(),
            'og_image' => $project->og_image ?: $project->hero_image ?: SiteSetting::get('default_og_image'),
            'robots' => $this->robotsDirective($project->isIndexable(), $project->isFollowable()),
        ];
    }

    public function robotsDirective(bool $index, bool $follow): string
    {
        $indexPart = $index ? 'index' : 'noindex';
        $followPart = $follow ? 'follow' : 'nofollow';

        return "{$indexPart}, {$followPart}";
    }

    /**
     * @return array<string, mixed>
     */
    public function personJsonLd(): array
    {
        $siteName = SiteSetting::get('site_name', config('app.name'));

        return [
            '@context' => 'https://schema.org',
            '@type' => 'Person',
            'name' => $siteName,
            'url' => config('app.url'),
            'jobTitle' => SiteSetting::get('job_title', 'Senior Digital Strategist'),
            'address' => [
                '@type' => 'PostalAddress',
                'addressLocality' => 'Minneapolis',
                'addressRegion' => 'MN',
                'addressCountry' => 'US',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function websiteJsonLd(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => SiteSetting::get('site_name', config('app.name')),
            'url' => config('app.url'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function articleJsonLd(Post $post): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $post->title,
            'description' => $post->excerpt,
            'datePublished' => $post->published_at?->toIso8601String(),
            'dateModified' => ($post->display_updated_at ?? $post->updated_at)?->toIso8601String(),
            'author' => [
                '@type' => 'Person',
                'name' => $post->author?->name ?? SiteSetting::get('site_name'),
            ],
            'mainEntityOfPage' => url('/insights/'.$post->slug),
        ];
    }

    /**
     * @param  array<int, array{label: string, url: string}>  $items
     * @return array<string, mixed>
     */
    public function breadcrumbJsonLd(array $items): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => collect($items)->values()->map(fn ($item, $index) => [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $item['label'],
                'item' => $item['url'],
            ])->all(),
        ];
    }
}
