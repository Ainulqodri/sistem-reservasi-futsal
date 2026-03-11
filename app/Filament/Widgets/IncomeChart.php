<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class IncomeChart extends ChartWidget
{
    protected static ?string $heading = 'Tren Pendapatan Bulanan';
    protected static ?int $sort = 1; // Urutan widget di dashboard

    protected function getData(): array
    {
        // PERBAIKAN: Gunakan Trend::query() dan masukkan Eloquent Builder langsung di dalamnya
        $data = Trend::query(
            Booking::query()->where('status', 'confirmed') // Filter langsung di sini
        )
            ->between(
                start: now()->startOfYear(),
                end: now()->endOfYear(),
            )
            ->perMonth()
            ->sum('total_price');

        return [
            'datasets' => [
                [
                    'label' => 'Pendapatan (Rp)',
                    'data' => $data->map(fn(TrendValue $value) => $value->aggregate),
                    'borderColor' => '#10b981',
                    'fill' => 'start',
                ],
            ],
            'labels' => $data->map(fn(TrendValue $value) => $value->date),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
