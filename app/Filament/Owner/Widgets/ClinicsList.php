<?php

namespace App\Filament\Owner\Widgets;

use Filament\Forms;
use Filament\Tables;
use Filament\Infolists;
use Filament\Tables\Table;
use Filament\Infolists\Infolist;
use Illuminate\Database\Eloquent\Model;
use App\Filament\Resources\ClinicResource;
use Filament\Widgets\TableWidget as BaseWidget;

class ClinicsList extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(ClinicResource::getEloquentQuery())
            ->defaultPaginationPageOption(5)
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('address')
                    ->searchable(),
                // Tables\Columns\TextColumn::make('zip')
                //     ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->infolist(function (Infolist $infolist, Model $record) {
                        return self::infolist($infolist, $record);
                    }),
            ]);
    }

    public static function infolist(Infolist $infolist, Model $record): Infolist
    {
        $services = $record->services;
        
        return $infolist
            ->schema([
                Infolists\Components\TextEntry::make('name'),
                Infolists\Components\TextEntry::make('address'),
                Infolists\Components\TextEntry::make('phone'),
                Infolists\Components\RepeatableEntry::make('services')
                    ->schema([
                        Infolists\Components\TextEntry::make('services.name'),
                        Infolists\Components\TextEntry::make('price'),
                    ])
                    ->columns(2)
            ])
            ->columns(1)
            ->inlineLabel();
    }
}
