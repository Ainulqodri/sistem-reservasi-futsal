<?php

namespace App\Livewire;

use App\Models\Booking;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;
// use Livewire\Attributes\On;

class BookingHistory extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public function render()
    {
        // Ambil data booking milik user yang sedang login
        // Diurutkan dari yang terbaru
        // Diload bersama relasi 'lapangan' agar hemat query
        $bookings = Booking::with('lapangan')
            ->where('user_id', Auth::id())
            ->latest() // atau ->orderBy('created_at', 'desc')
            ->paginate(5);

        return view('livewire.booking-history', [
            'bookings' => $bookings
        ]);
    }

    // #[On('batal-booking')]
    // Ubah parameter dari $id menjadi $orderId biar jelas
    public function cancelBooking($orderId)
    {
        // 1. Cek apakah ada data booking dengan ORDER ID tersebut yang masih pending
        // Kita pakai exists() biar hemat query
        $exists = Booking::where('order_id', $orderId)
                         ->where('user_id', Auth::id())
                         ->where('status', 'pending')
                         ->exists();

        if (!$exists) {
            // Jika tidak ketemu (mungkin sudah lunas, sudah batal duluan, atau salah ID)
            Log::error("Gagal batal. Order ID $orderId tidak valid atau status bukan pending.");
            $this->dispatch('show-error', ['message' => 'Gagal: Data tidak ditemukan atau status sudah berubah.']);
            return;
        }

        // 2. Lakukan Update Massal (Batch Update)
        // Semua slot jam dengan order_id ini akan diubah jadi cancelled sekaligus
        Booking::where('order_id', $orderId)
               ->where('user_id', Auth::id())
               ->update(['status' => 'cancelled']);

        Log::info("Sukses membatalkan seluruh slot untuk Order ID $orderId");

        $this->dispatch('show-success', ['message' => 'Booking berhasil dibatalkan.']);
    }
}
