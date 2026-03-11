<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Booking;
use App\Models\Lapangan; // <--- Jangan lupa import Model Lapangan
use Carbon\Carbon;

class CekJadwal extends Component
{
    public $selectedDate;
    public $selectedLapangan; // <--- Properti baru untuk menyimpan ID lapangan yang dipilih

    public function mount()
    {
        // 1. Default tanggal hari ini
        $this->selectedDate = now()->format('Y-m-d');

        // 2. Default lapangan pertama yang ditemukan di database
        // Agar saat dibuka tidak kosongan
        $firstLapangan = Lapangan::first();
        if ($firstLapangan) {
            $this->selectedLapangan = $firstLapangan->id;
        }
    }

    public function render()
    {
        // A. Ambil Daftar Semua Lapangan untuk Dropdown
        $semuaLapangan = Lapangan::all();

        // B. Tentukan Jam Operasional (08:00 - 23:00)
        $jamOperasional = [];
        for ($i = 8; $i < 24; $i++) {
            $jamOperasional[] = str_pad($i, 2, '0', STR_PAD_LEFT) . ':00';
        }

        // C. Ambil Data Booking (Filter berdasarkan Tanggal DAN Lapangan)
        $bookedSlots = [];
        
        if ($this->selectedLapangan) {
            $bookedSlots = Booking::whereDate('tanggal', $this->selectedDate)
                ->where('lapangan_id', $this->selectedLapangan) // <--- PENTING: Filter ID Lapangan
                ->whereIn('status', ['confirmed', 'pending']) 
                ->get()
                ->map(function ($booking) {
                    return Carbon::parse($booking->jam_mulai)->format('H:i');
                })
                ->toArray();
        }

        return view('livewire.cek-jadwal', [
            'jamOperasional' => $jamOperasional,
            'bookedSlots' => $bookedSlots,
            'semuaLapangan' => $semuaLapangan // <--- Kirim data lapangan ke view
        ]);
    }
}