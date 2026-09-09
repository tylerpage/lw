<?php

namespace App\Filament\Widgets;

use App\Enums\PublishStatus;
use App\Models\Page;
use App\Models\Post;
use App\Models\Project;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DraftContentStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Draft pages', Page::query()->where('status', PublishStatus::Draft)->count()),
            Stat::make('Draft posts', Post::query()->where('status', PublishStatus::Draft)->count()),
            Stat::make('Draft projects', Project::query()->where('status', PublishStatus::Draft)->count()),
            Stat::make('Scheduled content',
                Page::query()->where('status', PublishStatus::Scheduled)->count()
                + Post::query()->where('status', PublishStatus::Scheduled)->count()
                + Project::query()->where('status', PublishStatus::Scheduled)->count()
            ),
        ];
    }
}
