<?php

namespace App\Filament\Widgets;

use App\Enums\ContactSubmissionStatus;
use App\Models\ContactSubmission;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class RecentSubmissionsWidget extends TableWidget
{
    protected static ?string $heading = 'Recent contact submissions';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                ContactSubmission::query()
                    ->where('status', ContactSubmissionStatus::New)
                    ->latest()
                    ->limit(5)
            )
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('email'),
                TextColumn::make('created_at')->dateTime(),
            ])
            ->paginated(false);
    }
}
