<?php

namespace App\Filament\Pages;

use App\Models\Booking;
use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Notifications\Notification;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;

class LaporanPendapatan extends Page implements HasForms
{
    use InteractsWithForms;

    // 1. Konfigurasi Menu Sidebar
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Laporan Keuangan';
    protected static ?string $title = 'Laporan Pendapatan';
    protected static ?string $navigationGroup = 'Laporan'; // Grup menu (opsional)
    protected static string $view = 'filament.pages.laporan-pendapatan';

    // 2. Properti untuk Form
    public ?array $data = [];

    // 3. Inisialisasi Form (Mount)
    public function mount(): void
    {
        // Set default tanggal awal & akhir bulan ini
        $this->form->fill([
            'dari_tanggal' => now()->startOfMonth()->format('Y-m-d'),
            'sampai_tanggal' => now()->endOfMonth()->format('Y-m-d'),
        ]);
    }

    // 4. Skema Form
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Filter Laporan')
                    ->description('Pilih periode tanggal untuk mencetak laporan.')
                    ->schema([
                        DatePicker::make('dari_tanggal')
                            ->label('Dari Tanggal')
                            ->required(),
                        DatePicker::make('sampai_tanggal')
                            ->label('Sampai Tanggal')
                            ->required(),
                    ])->columns(2),
            ])
            ->statePath('data');
    }

    // 5. Fungsi Cetak PDF
    public function cetak()
    {
        $data = $this->form->getState();

        // Query Data
        $bookings = Booking::query()
            ->where('status', 'confirmed') // Hanya yang LUNAS
            ->whereDate('tanggal', '>=', $data['dari_tanggal'])
            ->whereDate('tanggal', '<=', $data['sampai_tanggal'])
            ->with(['user', 'lapangan'])
            ->orderBy('tanggal', 'asc')
            ->get();

        // Validasi jika data kosong
        if ($bookings->isEmpty()) {
            Notification::make()
                ->title('Tidak ada data')
                ->body('Tidak ada transaksi lunas pada periode tanggal tersebut.')
                ->warning()
                ->send();
            return;
        }

        // Load View PDF (Pastikan file view pdf/laporan_keuangan.blade.php SUDAH ADA)
        $pdf = Pdf::loadView('pdf.laporan_keuangan', [
            'bookings' => $bookings,
            'startDate' => Carbon::parse($data['dari_tanggal'])->translatedFormat('d F Y'),
            'endDate' => Carbon::parse($data['sampai_tanggal'])->translatedFormat('d F Y'),
        ]);

        // Download File
        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, 'Laporan-Pendapatan-' . now()->timestamp . '.pdf');
    }
}