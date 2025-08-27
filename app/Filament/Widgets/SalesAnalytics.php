<?php
namespace App\Filament\Widgets;
use App\Models\Sale\Sale;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
class SalesAnalytics extends ChartWidget
{    
    protected static ?int $sort = 3;
    protected static ?string $heading = 'Sales Revenue';
    protected static ?string $pollingInterval = null;
    protected int|string|array $columnSpan = 'full';

    public ?string $filter = 'month';

    protected function getFilters(): ?array
    {
        return [
            'week' => 'Last week',
            'month' => 'Last month',
            'year' => 'This year',
        ];
    }

    protected function getData(): array
    {
        $startDate = $this->getStartDate();
        $endDate = $this->getEndDate();
        $groupingPeriod = $this->getGroupingPeriod();
    
        // Cost of sold items (now using product's unit_cost)
        $saleItemsQuery = \App\Models\Sale\SaleItem::whereHas('sale', function ($query) use ($startDate, $endDate) {
            $query->whereBetween('sale_date', [$startDate, $endDate]);
        })
        ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
        ->join('products', 'sale_items.product_id', '=', 'products.id'); // Join with products

        if ($groupingPeriod === 'day') {
            $salesData = \App\Models\Sale\SaleItem::query()
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->whereBetween('sales.sale_date', [$startDate, $endDate])
            ->selectRaw('DATE(sales.sale_date) as period, SUM(sale_items.total) as revenue')
            ->groupBy('period')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->period => (float) $item->revenue];
            });
            $costsData = $saleItemsQuery
                ->selectRaw('DATE(sales.sale_date) as period, SUM(sale_items.cogs) as cost') // Fixed to use unit_cost
                ->groupBy('period')
                ->get()
                ->pluck('cost', 'period');
    
            $periods = collect();
            $currentDate = clone $startDate;
            while ($currentDate <= $endDate) {
                $periods->push($currentDate->toDateString());
                $currentDate->addDay();
            }
    
            $labels = $periods->map(fn($date) => Carbon::parse($date)->format('M d'))->toArray();
        } else {
            $salesData = \App\Models\Sale\SaleItem::query()
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->whereBetween('sales.sale_date', [$startDate, $endDate])
            ->selectRaw('YEAR(sales.sale_date) as year, MONTH(sales.sale_date) as month, SUM(sale_items.total) as revenue')
            ->groupBy('year', 'month')
            ->get()
            ->mapWithKeys(function ($item) {
                $key = $item->year . '-' . str_pad($item->month, 2, '0', STR_PAD_LEFT);
                return [$key => (float) $item->revenue];
            });
    
            $costsData = $saleItemsQuery
                ->selectRaw('YEAR(sales.sale_date) as year, MONTH(sales.sale_date) as month, SUM(sale_items.cogs) as cost')
                ->groupBy('year', 'month')
                ->get()
                ->mapWithKeys(fn($item) => [
                    $item->year . '-' . str_pad($item->month, 2, '0', STR_PAD_LEFT) => $item->cost
                ]);
    
            $periods = collect();
            $currentDate = (clone $startDate)->startOfMonth();
            $endDateMonth = (clone $endDate)->startOfMonth();
    
            while ($currentDate <= $endDateMonth) {
                $key = $currentDate->format('Y-m');
                $periods->push($key);
                $currentDate->addMonth();
            }
    
            $labels = $periods->map(fn($ym) => Carbon::parse($ym . '-01')->format('M Y'))->toArray();
        }
    
        $revenueData = [];
        $costData = [];
        $profitData = [];
    
        foreach ($periods as $period) {
            $revenue = $salesData->get($period, 0);
            $cost = $costsData->get($period, 0);
    
            $revenueData[] = $revenue;
            $costData[] = $cost;
            $profitData[] = $revenue - $cost;
        }
    
        return [
            'datasets' => [
                [
                    'label' => 'Sales Revenue',
                    'data' => $revenueData,
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => '#3b82f620',
                ],
                [
                    'label' => 'Cost of Goods Sold',
                    'data' => $costData,
                    'borderColor' => '#ef4444',
                    'backgroundColor' => '#ef444420',
                ],
                [
                    'label' => 'Gross Profit',
                    'data' => $profitData,
                    'borderColor' => '#10b981',
                    'backgroundColor' => '#10b98120',
                ],
            ],
            'labels' => $labels,
        ];
    }
    
    protected function getStartDate(): Carbon
    {
        return match($this->filter) {
            'today' => now()->startOfDay(),
            'week' => now()->subDays(6)->startOfDay(), // last 6 days + today = 7 days
            'month' => now()->subDays(29)->startOfDay(), // last 29 days + today = 30 days
            'year' => now()->startOfYear(), // already fine
            default => now()->subDays(29)->startOfDay(),
        };
    }

    protected function getEndDate(): Carbon
    {
        return now()->endOfDay();
    }

protected function getGroupingPeriod(): string
{
    return match($this->filter) {
        'week', 'month' => 'day',
        'year' => 'month',
        default => 'day',
    };
}

    protected function getType(): string
    {
        return 'line';
    }
}