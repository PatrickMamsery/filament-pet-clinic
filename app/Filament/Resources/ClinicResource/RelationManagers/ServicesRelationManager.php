<?php

namespace App\Filament\Resources\ClinicResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use App\Models\Clinic;
use App\Models\Service;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\ClinicService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class ServicesRelationManager extends RelationManager
{
    protected static string $relationship = 'services';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\Textarea::make('description')
                        ->nullable(),
                ])
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('description'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    // ->using(function (array $data, string $model): Model {
                    //     dd($this->ownerRecord->id);
                    //     return $model::create($data);
                    // })
                    ->after(function (Service $record, array $data) {
                        $clinicData = [
                            'clinic_id' => $this->ownerRecord->id,
                            'service_id' => $record->id,
                        ];

                        ClinicService::create($clinicData);
                    })
                    ->createAnother(false),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public function mount(): void
    {
        parent::mount();

        // Accessing the parent record ID
        $parentRecordId = $this->ownerRecord->id;

        // Now you can use $parentRecordId for any purpose within the relationship manager
    }
}
