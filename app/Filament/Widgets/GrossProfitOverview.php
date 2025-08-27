<?php

namespace App\Filament\Widgets;

use App\Models\Sale\Sale;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class GrossProfitOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $today = Carbon::today();
        $currentYear = now()->year;
        $currentMonth = now()->month;

        // --- Monthly Gross Profit (from sale_items)
        $monthlyProfits = DB::table('sale_items')
            ->selectRaw('MONTH(created_at) as month, SUM(total) as revenue, SUM(cogs) as cost')
            ->whereYear('created_at', $currentYear)
            ->groupBy('month')
            ->get()
            ->mapWithKeys(function ($item) {
                return [
                    (int) $item->month => $item->revenue - $item->cost
                ];
            })
            ->toArray();

        // --- Today's Gross Profit
        $todayProfit = DB::table('sale_items')
            ->whereDate('created_at', $today)
            ->selectRaw('SUM(total) - SUM(cogs) as profit')
            ->value('profit') ?? 0;

        // --- Current Month's Gross Profit
        $currentMonthProfit = $monthlyProfits[$currentMonth] ?? 0;

        // --- Total Gross Profit
        $totalProfit = DB::table('sale_items')
            ->selectRaw('SUM(total) - SUM(cogs) as profit')
            ->value('profit') ?? 0;

        return [
            Stat::make("Today's Gross Profit", '$' . number_format($todayProfit, 2))
                ->icon('heroicon-o-currency-dollar')
                ->chart($this->getDailyChartData())
                ->chartColor('white')
                ->backgroundColor('success'),

            Stat::make('Monthly Gross Profit', '$' . number_format($currentMonthProfit, 2))
                ->icon('heroicon-o-chart-bar')
                ->chart($this->getMonthlyProfitChartData($monthlyProfits))
                ->chartColor('white')
                ->iconColor('white')
                ->backgroundColor('warning'),

            Stat::make('Total Gross Profit', '$' . number_format($totalProfit, 2))
                ->icon('heroicon-o-banknotes')
        ];
    }

    private function getDailyChartData(): array
    {
        $pastDays = 6;
        $startDate = now()->subDays($pastDays)->startOfDay();

        // Pre-fill all 7 days with 0 profit
        $dates = [];
        for ($i = 0; $i <= $pastDays; $i++) {
            $date = now()->subDays($pastDays - $i)->toDateString();
            $dates[$date] = 0;
        }

        // Query daily profit
        $dailyProfits = DB::table('sale_items')
            ->whereDate('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, SUM(total) - SUM(cogs) as profit')
            ->groupBy('date')
            ->pluck('profit', 'date')
            ->toArray();

        // Merge with pre-filled array
        foreach ($dailyProfits as $date => $profit) {
            $dates[$date] = $profit;
        }

        return array_values(array_map(fn($profit) => $profit / 100, $dates));
    }

    private function getMonthlyProfitChartData(array $monthlyProfits): array
    {
        return collect($monthlyProfits)
            ->sortKeys()
            ->map(fn($profit) => $profit / 1000)
            ->values()
            ->toArray();
    }
}
