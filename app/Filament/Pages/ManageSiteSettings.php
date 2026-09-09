<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use BackedEnum;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ManageSiteSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?string $navigationLabel = 'Site Settings';

    protected static ?string $title = 'Site Settings';

    protected string $view = 'filament.pages.manage-site-settings';

    /** @var array<string, mixed> */
    public array $data = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->isSuperAdmin() ?? false;
    }

    public function mount(): void
    {
        $this->form->fill([
            'site_name' => SiteSetting::get('site_name'),
            'job_title' => SiteSetting::get('job_title'),
            'short_bio' => SiteSetting::get('short_bio'),
            'contact_email' => SiteSetting::get('contact_email'),
            'contact_availability' => SiteSetting::get('contact_availability'),
            'linkedin_url' => SiteSetting::get('linkedin_url'),
            'analytics_enabled' => SiteSetting::get('analytics_enabled', false),
            'easter_eggs_enabled' => SiteSetting::get('easter_eggs_enabled', false),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('site_name')->required(),
                TextInput::make('job_title'),
                Textarea::make('short_bio')->columnSpanFull(),
                TextInput::make('contact_email')->email(),
                Textarea::make('contact_availability')->columnSpanFull(),
                TextInput::make('linkedin_url')->url(),
                Toggle::make('analytics_enabled'),
                Toggle::make('easter_eggs_enabled'),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();

        foreach ($state as $key => $value) {
            SiteSetting::set($key, $value);
        }

        Notification::make()->title('Settings saved')->success()->send();
    }
}
