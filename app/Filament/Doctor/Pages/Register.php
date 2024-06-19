<?php

namespace App\Filament\Doctor\Pages;

use Filament\Forms;
use App\Models\Role;
use App\Models\Clinic;
use Filament\Facades\Filament;
use Illuminate\Auth\Events\Registered;
use Filament\Forms\Components\Component;
use Filament\Notifications\Notification;
use Filament\Pages\Auth\Register as BaseRegisterPage;
use Filament\Http\Responses\Auth\Contracts\RegistrationResponse;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;

class Register extends BaseRegisterPage
{
    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema([
                        $this->getClinicFormComponent(),
                        $this->getNameFormComponent(),
                        $this->getEmailFormComponent(),
                        Forms\Components\TextInput::make('phone')
                            ->required()
                            ->tel(),
                        $this->getPasswordFormComponent(),
                        $this->getPasswordConfirmationFormComponent(),
                        $this->getRoleFormComponent(),
                    ])
                    ->statePath('data'),
            ),
        ];
    }

    protected function getClinicFormComponent(): Component
    {
        return Forms\Components\Group::make()
            ->schema([
                Forms\Components\TextInput::make('clinic_name')
                    ->label('Clinic Name')
                    ->required(),
                Forms\Components\TextInput::make('clinic_address')
                    ->label('Clinic Address')
                    ->required(),
                Forms\Components\TextInput::make('clinic_phone')
                    ->label('Clinic Phone')
                    ->required()
                    ->tel(),
            ]);
    }

    protected function getRoleFormComponent(): Component
    {
        return Forms\Components\Hidden::make('role_id')
            ->default(Role::where('name', 'doctor')->first()->id)
            ->required();
    }

    public function register(): ?RegistrationResponse
    {
        try {
            $this->rateLimit(2);
        } catch (TooManyRequestsException $exception) {
            Notification::make()
                ->title(__('filament-panels::pages/auth/register.notifications.throttled.title', [
                    'seconds' => $exception->secondsUntilAvailable,
                    'minutes' => ceil($exception->secondsUntilAvailable / 60),
                ]))
                ->body(array_key_exists('body', __('filament-panels::pages/auth/register.notifications.throttled') ?: []) ? __('filament-panels::pages/auth/register.notifications.throttled.body', [
                    'seconds' => $exception->secondsUntilAvailable,
                    'minutes' => ceil($exception->secondsUntilAvailable / 60),
                ]) : null)
                ->danger()
                ->send();

            return null;
        }

        $data = $this->form->getState();

        // separate clinic data
        $clinicData = [
            'name' => $data['clinic_name'],
            'address' => $data['clinic_address'],
            'phone' => $data['clinic_phone'],
        ];

        // remove clinic data from user data
        unset($data['clinic_name']);
        unset($data['clinic_address']);
        unset($data['clinic_phone']);

        // create clinic
        $clinic = Clinic::firstOrCreate($clinicData);

        // create user
        $user = $this->getUserModel()::create($data);

        // attach user to clinic
        $clinic->users()->attach($user->id);

        app()->bind(
            \Illuminate\Auth\Listeners\SendEmailVerificationNotification::class,
            \Filament\Listeners\Auth\SendEmailVerificationNotification::class,
        );

        event(new Registered($user));

        Filament::auth()->login($user);

        session()->regenerate();

        return app(RegistrationResponse::class);
    }
}
