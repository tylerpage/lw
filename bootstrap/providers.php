<?php

use App\Providers\AppServiceProvider;
use App\Providers\ContentAssistantServiceProvider;
use App\Providers\Filament\AdminPanelProvider;

return [
    AppServiceProvider::class,
    ContentAssistantServiceProvider::class,
    AdminPanelProvider::class,
];
