<?php

namespace App\Filament\Resources\ScheduleResource\Pages;

use App\Enums\DaysOfTheWeek;
use App\Filament\Resources\ScheduleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use App\Models\Schedule;

class ListSchedules extends ListRecords
{
    protected static string $resource = ScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return collect(DaysOfTheWeek::cases())->mapWithKeys(function ($day) {
            $dayName = $day->name;
            return [
                $dayName => Tab::make()
                    ->modifyQueryUsing(fn (Builder $query) => $query->where('owner_id', auth()->user()->id)->where('day_of_week', $day))
                    ->badge($this->getBadgeCount($day->value)),
            ];
        })->toArray();
    }

    protected function getBadgeCount(string $day): ?int
    {
        return Schedule::query()
            ->where('day_of_week', $day)
            ->where('owner_id', auth()->user()->id)
            ->count() ?: null;
    }

    public function getDefaultActiveTab(): string | int | null
    {
        return Carbon::today()->format('l');
    }
}
