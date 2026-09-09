<?php

namespace App\Providers;

use App\ContentAssistant\Contracts\ContentAssistantGateway;
use App\ContentAssistant\Services\FakeContentAssistantGateway;
use Illuminate\Support\ServiceProvider;

class ContentAssistantServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ContentAssistantGateway::class, function () {
            return match (config('content-assistant.driver')) {
                'fake' => new FakeContentAssistantGateway,
                default => new FakeContentAssistantGateway,
            };
        });
    }
}
