<?php

namespace App\Filament\Owner\Resources;

use Carbon\Carbon;
use Filament\Forms;
use App\Models\Role;
use App\Models\Slot;
use App\Models\User;
use Filament\Tables;
use App\Enums\PetType;
use Filament\Forms\Get;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Appointment;
use App\Support\AvatarOptions;
use App\Enums\AppointmentStatus;
use Filament\Resources\Resource;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Actions\Action;
use App\Filament\Owner\Resources\AppointmentResource\Pages;

class AppointmentResource extends Resource
{
    protected static ?string $model = Appointment::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        $doctorRole = Role::whereName('doctor')->first();

        return $form
            ->schema([
                Forms\Components\Select::make('pet_id')
                    ->relationship('pet', 'name')
                    ->required()
                    ->createOptionForm([
                        Forms\Components\FileUpload::make('avatar')
                            ->image()
                            ->imageEditor(),
                        Forms\Components\TextInput::make('name')
                            ->required(),
                        Forms\Components\DatePicker::make('date_of_birth')
                            ->required(),
                        Forms\Components\Select::make('type')
                            ->options(PetType::class)
                            ->required(),
                        Forms\Components\Hidden::make('owner_id')
                            ->default(auth()->user()->id),
                    ])->createOptionAction(function (Action $action) {
                        return $action
                            ->modalHeading('Create Pet')
                            ->modalSubmitActionLabel('Create Pet');
                    }),
                Forms\Components\Select::make('clinic_id')
                    ->relationship('clinic', 'name')
                    ->required(),
                Forms\Components\DatePicker::make('date')
                    ->live()
                    // ->disabled(fn (Get $get) => !($get('clinic_id')))
                    ->required(),
                Forms\Components\Select::make('doctor_id')
                    ->required()
                    ->hidden(fn (Get $get) => blank($get('date')))
                    ->live()
                    ->allowHtml()
                    ->options(function (Get $get) use ($doctorRole) {
                        $doctors = User::whereBelongsTo($doctorRole)
                            ->whereHas('schedules', function (Builder $query) use ($get) {
                                $dayOfTheWeek = Carbon::parse($get('date'))->dayOfWeek;
                                $query
                                    ->where('day_of_week', $dayOfTheWeek)
                                    ->where('clinic_id', $get('clinic_id'));
                            })
                            ->get();
                        return $doctors->mapWithKeys(function ($doctor) {
                            return [$doctor->getKey() => AvatarOptions::getOptionString($doctor)];
                        })->toArray();
                    })
                    ->afterStateUpdated(function (Forms\Set $set) {
                        $set('slot_id', null);
                    })
                    ->helperText(function ($component) {
                        if (!$component->getOptions()) {
                            return new HtmlString(
                                '<span class="text-sm text-danger-600 dark:text-danger-400">No doctors available. Please select a different clinic or date</span>'
                            );
                        }

                        return '';
                    }),
                Forms\Components\Select::make('slot_id')
                    ->label('Slot')
                    ->options(function (Forms\Get $get): array {
                        $doctorId = $get('doctor_id');
                        $date = $get('date');

                        if (blank($doctorId) || blank($date)) {
                            return [];
                        }

                        $dayOfTheWeek = Carbon::parse($date)->dayOfWeek;

                        $slots = Slot::query()
                            ->whereHas('schedule', function (Builder $query) use ($doctorId, $dayOfTheWeek, $get) {
                                $query->where('clinic_id', $get('clinic_id'))
                                    ->where('day_of_week', $dayOfTheWeek)
                                    ->where('owner_id', $doctorId);
                            })
                            ->whereDoesntHave('appointment', function (Builder $query) use ($date) {
                                $query->where('date', $date);
                            })
                            ->get();

                        return $slots->mapWithKeys(function ($slot) {
                            return [$slot->id => $slot->formattedTime];
                        })->toArray();
                    })
                    ->hidden(fn (Get $get) => blank($get('doctor_id')))
                    ->preload()
                    ->live()
                    ->searchable()
                    ->required(),
                Forms\Components\TextInput::make('description')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('pet.avatar')
                    ->circular(),
                Tables\Columns\TextColumn::make('pet.name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('description')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('doctor.name')
                    ->label('Doctor')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('clinic.name')
                    ->label('Clinic')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('date')
                    ->date('M d, Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('slot.formatted_time')
                    ->label('Time')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->sortable()
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAppointments::route('/'),
            'create' => Pages\CreateAppointment::route('/create'),
            'edit' => Pages\EditAppointment::route('/{record}/edit'),
        ];
    }
}
