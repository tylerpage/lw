<?php

namespace App\Providers;

use App\ContentAssistant\Contracts\ContentAssistantGateway;
use App\ContentAssistant\Services\FakeContentAssistantGateway;
use App\ContentAssistant\Services\LaravelAiContentAssistantGateway;
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
    }
}
