<?php

namespace App\Providers;

use App\ContentAssistant\Contracts\ContentAssistantGateway;
use App\ContentAssistant\Contracts\SeoGenerationGateway;
use App\ContentAssistant\Services\FakeContentAssistantGateway;
use App\ContentAssistant\Services\LaravelAiContentAssistantGateway;
use App\ContentAssistant\Services\LaravelAiSeoGenerationGateway;
use App\ContentAssistant\Services\RuleBasedSeoGenerationGateway;
use Illuminate\Support\ServiceProvider;

class ContentAssistantServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ContentAssistantGateway::class, function ($app) {
            return match (config('content-assistant.driver')) {
                'ai', 'openai', 'laravel-ai' => $app->make(LaravelAiContentAssistantGateway::class),
                default => new FakeContentAssistantGateway,
            };
        });

        $this->app->singleton(SeoGenerationGateway::class, function ($app) {
            return match (config('content-assistant.driver')) {
                'ai', 'openai', 'laravel-ai' => $app->make(LaravelAiSeoGenerationGateway::class),
                default => $app->make(RuleBasedSeoGenerationGateway::class),
            };
        });
    }
}
