<?php

namespace App\Filament\Doctor\Pages;

use Filament\Forms;
use App\Models\Role;
use Filament\Forms\Form;
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

    protected function getRoleFormComponent(): Component
    {
        return Forms\Components\Select::make('role_id')
            ->label('Role')
            ->options(Role::all()->pluck('name', 'id')->toArray())
            ->disabled()
            ->default(Role::where('name', 'doctor')->first()->id)
            ->required();
    }
}
