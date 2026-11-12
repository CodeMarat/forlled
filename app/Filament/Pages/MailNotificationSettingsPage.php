<?php

namespace App\Filament\Pages;

use App\Models\MailNotificationSetting;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;

class MailNotificationSettingsPage extends Page
{
    protected string $view = 'filament.pages.mail-notification-settings-page';

    protected static ?string $title = 'Mail settings';

    protected static bool $shouldRegisterNavigation = false;

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    public function mount(): void
    {
        $settings = MailNotificationSetting::query()->find(1);

        $this->form->fill([
            'partner_request_email' => $settings?->partner_request_email ?: config('services.partner_requests.recipient'),
            'contact_us_request_email' => $settings?->contact_us_request_email ?: config('services.contact_us_requests.recipient'),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Become a Partner')
                    ->description('Email address for new partner requests.')
                    ->schema([
                        TextInput::make('partner_request_email')
                            ->label('Recipient email')
                            ->email()
                            ->required()
                            ->maxLength(255),
                    ]),
                Section::make('Contact Us')
                    ->description('Email address for new contact requests.')
                    ->schema([
                        TextInput::make('contact_us_request_email')
                            ->label('Recipient email')
                            ->email()
                            ->required()
                            ->maxLength(255),
                    ]),
            ])
            ->statePath('data');
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            $this->getFormContentComponent(),
        ]);
    }

    public function save(): void
    {
        MailNotificationSetting::query()->updateOrCreate(
            ['id' => 1],
            $this->form->getState(),
        );

        Notification::make()
            ->title('Mail settings saved')
            ->success()
            ->send();
    }

    protected function getFormContentComponent(): Component
    {
        return Form::make([
            Section::make()
                ->schema([EmbeddedSchema::make('form')])
                ->footerActions([
                    Action::make('save')
                        ->label('Save')
                        ->submit('save'),
                ])
                ->footerActionsAlignment(Alignment::End),
        ])
            ->id('form')
            ->livewireSubmitHandler('save');
    }
}
