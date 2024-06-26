<?php

namespace App\Filament\Doctor\Widgets;

use App\Models\Appointment;
use App\Models\Pet;
use App\Models\Schedule;
use Carbon\Carbon;
use App\Traits\OverviewTrait;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class StatsOverview extends BaseWidget
{
    use InteractsWithPageFilters, OverviewTrait;

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $startDate = $this->getStartDate();
        $endDate = $this->getEndDate();

        $pets = $this->getModelCount(Pet::class, $startDate, $endDate);
        $appointments = $this->getModelCount(Appointment::class, $startDate, $endDate);
        $schedules = $this->getModelCount(Schedule::class, $startDate, $endDate);

        return [
            $this->createStat('Pets', $pets, Pet::class),
            $this->createStat('Appointments', $appointments, Appointment::class),
            $this->createStat('Schedules', $schedules, Schedule::class),
        ];
    }

    protected function createStat(string $label, int $value, string $modelClass): Stat
    {
        $descriptionData = $this->getSummaryData($modelClass);
        $diffPercentage = $descriptionData['diff'];
        $diffIcon = $diffPercentage > 0 ? 'heroicon-o-arrow-trending-up' : 'heroicon-o-arrow-trending-down';
        $diffText = $diffPercentage . '% ' . ($diffPercentage > 0 ? 'increase' : 'decrease') . ' from last month';

        return Stat::make($label, formatNumber($value))
            ->description($diffText)
            ->descriptionIcon($diffIcon)
            ->chart($this->getChartData($modelClass)['data'])
            ->color($diffPercentage > 0 ? 'success' : 'danger');
    }

    protected function getStartDate(): ?Carbon
    {
        return !is_null($this->filters['startDate'] ?? null) ?
            Carbon::parse($this->filters['startDate']) :
            null;
    }

    protected function getEndDate(): Carbon
    {
        return !is_null($this->filters['endDate'] ?? null) ?
            Carbon::parse($this->filters['endDate']) :
            now();
    }
}
