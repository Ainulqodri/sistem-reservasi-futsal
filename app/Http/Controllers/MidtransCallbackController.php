<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\Notification;

class MidtransCallbackController extends Controller
{
    public function callback(Request $request)
    {
        // 1. Set Konfigurasi Midtrans
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;

        // 2. Buat Instance Notification Midtrans
        try {
            $notification = new Notification();
        } catch (\Exception $e) {
            Log::error('Midtrans Error: ' . $e->getMessage());
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        // 3. Ambil data transaksi dari Midtrans
        $transactionStatus = $notification->transaction_status;
        $paymentType = $notification->payment_type;
        $fraudStatus = $notification->fraud_status;
        $orderId = $notification->order_id;

        // 4. Cari Data Booking
        // PERBAIKAN: Gunakan get() dan with() karena kita butuh data user & lapangan untuk WA
        $bookings = Booking::with(['user', 'lapangan'])
            ->where('order_id', $orderId)
            ->get();

        if ($bookings->isEmpty()) {
            return response()->json(['message' => 'Booking order not found'], 404);
        }

        // Ambil satu sampel untuk pengecekan status awal & data user
        $sampleBooking = $bookings->first();

        // Log untuk debugging
        Log::info("Callback masuk Order ID: $orderId | Status Midtrans: $transactionStatus | Status DB Awal: {$sampleBooking->status}");

        // 5. Logika Mapping Status
        $newStatus = null;

        if ($transactionStatus == 'capture') {
            if ($paymentType == 'credit_card') {
                if ($fraudStatus == 'challenge') {
                    $newStatus = 'pending';
                } else {
                    $newStatus = 'confirmed';
                }
            }
        } elseif ($transactionStatus == 'settlement') {
            $newStatus = 'confirmed';
        } elseif ($transactionStatus == 'pending') {
            $newStatus = 'pending';
        } elseif ($transactionStatus == 'deny') {
            $newStatus = 'cancelled';
        } elseif ($transactionStatus == 'expire') {
            $newStatus = 'cancelled';
        } elseif ($transactionStatus == 'cancel') {
            $newStatus = 'cancelled';
        }

        // 6. Update Database & Kirim Notifikasi
        if ($newStatus) {
            
            // LOGIKA KHUSUS STATUS CONFIRMED (LUNAS)
            if ($newStatus == 'confirmed') {
                // Cek dulu: Apakah status di DB sebelumnya SUDAH confirmed?
                // Jika BELUM, maka update dan kirim WA.
                // Jika SUDAH, abaikan (supaya user tidak dapat WA spam berkali-kali)
                if ($sampleBooking->status != 'confirmed') {
                    
                    // A. Update Status
                    Booking::where('order_id', $orderId)->update(['status' => 'confirmed']);
                    Log::info("Order $orderId BERHASIL diupdate jadi CONFIRMED.");

                    // B. Kirim WA Lunas (Bungkus try-catch agar aman)
                    try {
                        $this->sendSuccessNotification($sampleBooking, $bookings);
                        Log::info("WA Lunas terkirim untuk $orderId");
                    } catch (\Exception $e) {
                        Log::error("Gagal kirim WA Lunas: " . $e->getMessage());
                    }
                } else {
                    Log::info("Order $orderId sudah confirmed sebelumnya. Skip update & WA.");
                }
            } 
            // LOGIKA UNTUK STATUS LAIN (Pending/Cancel/Expire)
            else {
                Booking::where('order_id', $orderId)->update(['status' => $newStatus]);
                Log::info("Order $orderId diupdate jadi $newStatus");
            }
        }

        return response()->json(['message' => 'Callback processed']);
    }

    // --- FUNGSI PRIVAT PENGIRIM WA ---
    private function sendSuccessNotification($bookingData, $allSlots)
    {
        $user = $bookingData->user; 
        $phone = $user->nomor_telepon ?? ''; // Fallback jika null

        if (empty($phone)) return;

        // Format Nomor HP
        $phone = ltrim($phone, '+');
        if (substr($phone, 0, 1) === '0') $phone = '62' . substr($phone, 1);

        // Susun List Jam
        $listJadwal = "";
        foreach ($allSlots as $slot) {
            // Asumsi format di DB "HH:MM:SS" atau datetime
            // Kita parse biar aman
            try {
                $jamMulai = \Carbon\Carbon::parse($slot->jam_mulai)->format('H:i');
                $jamSelesai = \Carbon\Carbon::parse($slot->jam_selesai)->format('H:i');
            } catch (\Exception $e) {
                // Fallback kalau parsing gagal
                $jamMulai = substr($slot->jam_mulai, 0, 5); 
                $jamSelesai = substr($slot->jam_selesai, 0, 5);
            }
            $listJadwal .= "• $jamMulai - $jamSelesai WIB\n";
        }

        // Format Tanggal
        try {
            $tanggalIndo = \Carbon\Carbon::parse($bookingData->tanggal)->translatedFormat('d F Y');
        } catch (\Exception $e) {
            $tanggalIndo = $bookingData->tanggal;
        }
        
        // Ambil Nama Lapangan (Pastikan relasi diload)
        $namaLapangan = $bookingData->lapangan->title ?? 'Lapangan Futsal';

        // ISI PESAN "LUNAS"
        $message = "*PEMBAYARAN BERHASIL!*\n\n";
        $message .= "Halo {$user->name}, pembayaran Anda telah kami terima.\n";
        $message .= "Booking Anda sudah *TERKONFIRMASI*.\n\n";

        $message .= "*E-Tiket Masuk:*\n";
        $message .= "No. Order: *{$bookingData->order_id}*\n";
        $message .= "Lapangan: *{$namaLapangan}*\n";
        $message .= "Tanggal: $tanggalIndo\n";
        $message .= "Jam Main:\n$listJadwal\n";
        $message .= "Status: *LUNAS (CONFIRMED)* ✅\n\n";

        $message .= "Harap tunjukkan pesan ini kepada petugas saat datang ke lokasi.\n";
        $message .= "_Terima kasih & Selamat bertanding!_";
        $message .= "\n\n> _Sent via Sistem Arena Futsal_"; 

        // Kirim Fonnte (Menggunakan HTTP Client Laravel)
        Http::withHeaders([
            'Authorization' => env('FONNTE_API_TOKEN'), // Sebaiknya pindah ke config('services.fonnte.token')
        ])->post('https://api.fonnte.com/send', [
            'target' => $phone,
            'message' => $message,
            'countryCode' => '62',
        ]);
    }
}