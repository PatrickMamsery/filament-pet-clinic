<?php

namespace App\Traits;

use Carbon\Carbon;
use App\Models\Schedule;
use App\Models\Appointment;
use Filament\Facades\Filament;

trait OverviewTrait
{
    /**
     * Add optional whereBelongsTo(Filament::getTenant()) condition to the query
     */
    protected static function addTenantCondition($query)
    {
        if (Filament::getTenant()) {
            return $query->whereBelongsTo(Filament::getTenant());
        }

        return $query;
    }

    /**
     * Get Model Count for metrics overview
     */
    public static function getModelCount($model, $startDate, $endDate): int
    {
        $query = $model::query();

        if (!is_null($startDate)) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        if ($model == Appointment::class || $model == Schedule::class) {
            $query = self::addTenantCondition($query);
        }

        return $query->count();
    }

    /**
     * Get the summary data for the metrics
     */
    public function getSummaryData($model, $currentMonth = null, $previousMonth = null)
    {
        if (is_null($currentMonth)) {
            $currentMonth = Carbon::now()->month;
        }

        if (is_null($previousMonth)) {
            $previousMonth = Carbon::now()->subMonth()->month;
        }

        $query = $model::query()
            ->whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', Carbon::now()->startOfYear());

        if ($model == Appointment::class || $model == Schedule::class) {
            $query = self::addTenantCondition($query);
        }

        $current = $query->count();

        $query = $model::query()
            ->whereMonth('created_at', $previousMonth)
            ->whereYear('created_at', Carbon::now()->startOfYear());

        if ($model == Appointment::class || $model == Schedule::class) {
            $query = self::addTenantCondition($query);
        }

        $previous = $query->count();

        return [
            'current' => $current,
            'previous' => $previous,
            'diff' => $this->calculateDiff($current, $previous)
        ];
    }

    /**
     * Calculate the percentage difference between two numbers
     */
    public function calculateDiff($current, $previous)
    {
        if ($previous == 0) {
            return 100;
        }
        return 100 * ($current - $previous) / $previous;
    }

    /**
     * Get the data for the charts
     */
    public static function getChartData($model)
    {
        $startDate = Carbon::now()->startOfYear();
        $endDate = Carbon::now();
        $dates = [];

        $dataArray = [];

        while ($startDate <= $endDate) {
            $formattedDate = $startDate->format('M-Y');
            $dates[] = $formattedDate;

            $query = $model::query()
                ->whereMonth('created_at', $startDate->month)
                ->whereYear('created_at', $startDate->year);

            if ($model == Appointment::class || $model == Schedule::class) {
                $query = self::addTenantCondition($query);
            }

            $data = $query->count();
            $dataArray[] = $data;

            $startDate->addMonth();
        }

        return [
            'labels' => $dates,
            'data' => $dataArray
        ];
    }
}
