<?php

namespace App\Filament\Pages\Tenancy;

use App\Models\Clinic;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Tenancy\RegisterTenant;

class RegisterClinic extends RegisterTenant
{
    public static function getLabel(): string
    {
        return 'Register clinic';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required(),
                Forms\Components\TextInput::make('address')
                    ->required(),
                Forms\Components\TextInput::make('phone')
                    ->tel()
                    ->required(),
            ]);
    }

    protected function handleRegistration(array $data): Clinic
    {
        // Before creating the clinic we need to check whether the clinic already exists
        $clinic = Clinic::where('name', $data['name'])
                        ->where('address', $data['address'])
                        ->where('phone', $data['phone'])
                        ->first();

        if ($clinic) {
            return $clinic;
        } else {
            $clinic = Clinic::create($data);
        }

        $clinic->users()->attach(auth()->user());

        return $clinic;
    }
}
