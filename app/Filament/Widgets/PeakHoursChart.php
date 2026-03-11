<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class PeakHoursChart extends ChartWidget
{
    protected static ?string $heading = 'Jam Paling Sibuk';
    protected static ?int $sort = 2; // Urutan ke-2

    protected function getData(): array
    {
        // Query untuk mengambil jam dari start_time dan menghitung jumlahnya
        // Contoh output yang diharapkan: [18 => 5, 19 => 10, 20 => 8] (Jam => Jumlah Booking)
        
        $data = Booking::select(DB::raw('HOUR(jam_mulai) as hour'), DB::raw('count(*) as total'))
            ->where('status', '!=', 'cancelled') // Jangan hitung yang batal
            ->groupBy('hour')
            ->orderBy('hour')
            ->pluck('total', 'hour')
            ->toArray();

        // Kita siapkan label jam dari 08:00 sampai 23:00 (sesuaikan jam operasional futsal)
        $labels = [];
        $dataset = [];

        // Loop manual agar grafik tetap rapih walau ada jam yang kosong bookingan
        for ($i = 8; $i <= 23; $i++) {
            $labels[] = sprintf('%02d:00', $i); // Format jadi "08:00", "09:00"
            $dataset[] = $data[$i] ?? 0; // Ambil data jika ada, jika tidak 0
        }

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Booking',
                    'data' => $dataset,
                    'backgroundColor' => '#3b82f6', // Warna biru
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
