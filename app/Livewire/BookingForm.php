<?php

namespace App\Livewire;

use App\Models\Booking;
use App\Models\Lapangan;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Livewire\Component;
use Midtrans\Config;
use Midtrans\Snap;

class BookingForm extends Component
{
    public $lapangan;
    public $selectedDate;

    public $selectedTimeSlots = [];
    public $availableDates = [];
    public $availableTimeSlots = [];
    public $bookedSlots = [];
    // public $operationalHours = [];
    public $totalPrice = 0;

    public $snapToken;

    protected $rules = [
        'selectedDate' => 'required|date',
        'selectedTimeSlot' => 'required',
    ];


    public function mount($lapanganId)
    {
        $this->lapangan = Lapangan::findOrFail($lapanganId);
        // $this->loadOperationalHours();
        $this->generateAvailableDates();
    }

    // public function loadOperationalHours()
    // {
    //     $jamBuka = Setting::where('key', 'jam_buka')->first();
    //     $jamTutup = Setting::where('key', 'jam_tutup')->first();

    //     $this->operationalHours = [
    //         'jam_buka' => $jamBuka ? $jamBuka->value : '08:00',
    //         'jam_tutup' => $jamTutup ? $jamTutup->value : '22:00',
    //     ];
    // }

    public function generateAvailableDates()
    {
        $dates = [];
        $today = Carbon::now();

        for ($i = 0; $i < 7; $i++) {
            $date = $today->copy()->addDays($i);
            $dates[] = [
                'date' => $date->format('Y-m-d'),
                'day' => $this->getDayName($date->dayOfWeek),
                'formatted' => $date->format('d M'),
                'full_date' => $date
            ];
        }

        $this->availableDates = $dates;
    }

    public function getDayName($dayOfWeek)
    {
        $days = [
            0 => 'Min',
            1 => 'Sen',
            2 => 'Sel',
            3 => 'Rab',
            4 => 'Kam',
            5 => 'Jum',
            6 => 'Sab',
        ];

        return $days[$dayOfWeek];
    }

    // selectDate
    public function selectDate($date)
    {
        $this->selectedDate = $date;
        $this->selectedTimeSlots = [];
        $this->totalPrice = 0;
        $this->updateAvailableTimeSlots();
    }

    // selectTimeSlot
    public function selectTimeSlot($slotKey)
    {
        // Cek apakah slot sudah ada di array (dicentang)
        if (in_array($slotKey, $this->selectedTimeSlots)) {
            // Jika sudah ada, hapus (Uncheck)
            $this->selectedTimeSlots = array_diff($this->selectedTimeSlots, [$slotKey]);
        } else {
            // Jika belum ada, tambahkan (Check)
            $this->selectedTimeSlots[] = $slotKey;
        }

        // Hitung ulang harga
        $this->calculatePrice();
    }

    // updateAvailableTimeSlots
    public function updateAvailableTimeSlots()
    {
        if (!$this->selectedDate) return;

        // gunakan cache untuk mempercepat query
        $this->bookedSlots = Cache::remember(
            "booked_{$this->lapangan->id}_{$this->selectedDate}",
            5, // cache 5 detik (bisa dinaikkan)
            function () {
                return Booking::where('lapangan_id', $this->lapangan->id)
                    ->where('tanggal', $this->selectedDate)
                    ->where('status', '!=', 'cancelled')
                    ->get()
                    ->map(function ($booking) {
                        return [
                            'jam_mulai' => \Carbon\Carbon::parse($booking->jam_mulai)->format('H:i'),
                            'jam_selesai' => \Carbon\Carbon::parse($booking->jam_selesai)->format('H:i'),
                        ];
                    })->toArray();
            }
        );

        $this->generateTimeSlots();
    }

    // generateTimeSlots
    public function generateTimeSlots()
    {
        // --- 1. AMBIL JAM BUKA & TUTUP DARI DATABASE (Via Class Setting) ---

        // Ambil jam buka, simpan di cache selama 60 menit
        $settingJamBuka = Cache::remember('setting_jam_buka', 60 * 60, function () {
            return Setting::where('key', 'jam_buka')->value('value');
        });

        // Ambil jam tutup, simpan di cache selama 60 menit
        $settingJamTutup = Cache::remember('setting_jam_tutup', 60 * 60, function () {
            return Setting::where('key', 'jam_tutup')->value('value');
        });

        // Fallback: Jika admin lupa isi setting, pakai default 07:00 - 24:00
        $jamBuka = \Carbon\Carbon::parse($settingJamBuka ?? '07:00');
        $jamTutup = \Carbon\Carbon::parse($settingJamTutup ?? '24:00');

        // --- 2. LOGIKA HARI LIBUR ---

        // Ambil tanggal libur
        $liburFitri = Cache::remember('setting_libur_fitri', 60 * 60, fn() => Setting::where('key', 'libur_idul_fitri')->value('value'));
        $liburAdha  = Cache::remember('setting_libur_adha', 60 * 60, fn() => Setting::where('key', 'libur_idul_adha')->value('value'));

        // Cek apakah tanggal yg dipilih user adalah hari libur?
        if ($this->selectedDate == $liburFitri || $this->selectedDate == $liburAdha) {
            $this->availableTimeSlots = []; // Kosongkan jadwal
            // Kirim sinyal error ke frontend (opsional)
            $this->dispatch('show-error', ['message' => 'Mohon maaf, lapangan tutup pada Hari Raya.']);
            return;
        }

        $sekarang = \Carbon\Carbon::now();
        $isToday  = $this->selectedDate === $sekarang->format('Y-m-d');

        $slots = [];

        while ($jamBuka->lt($jamTutup)) {
            $jamMulai = $jamBuka->format('H:i');
            $jamSelesai = $jamBuka->copy()->addHour()->format('H:i');

            $isBooked = $this->isSlotBooked($jamMulai, $jamSelesai);

            $waktuSlot = \Carbon\Carbon::parse($this->selectedDate . ' ' . $jamMulai);

            $isPassed = $isToday && $waktuSlot->lt($sekarang);

            // Panggil logic harga dinamis dari Model Lapangan
            $hargaFix = $this->lapangan->getHargaDinamis($this->selectedDate, $jamMulai);

            $slots[] = [
                'jam_mulai'   => $jamMulai,
                'jam_selesai' => $jamSelesai,
                'label'       => $jamMulai . ' - ' . $jamSelesai,
                'price'       => $hargaFix,
                'is_booked'   => $isBooked,
                'slot_key'    => $jamMulai . '-' . $jamSelesai,
                'is_passed'   => $isPassed,
            ];

            $jamBuka->addHour();
        }

        $this->availableTimeSlots = $slots;
    }

    // isSlotBooked
    public function isSlotBooked($mulai, $selesai)
    {
        $start = strtotime($mulai);
        $end = strtotime($selesai);

        foreach ($this->bookedSlots as $booked) {
            if (
                $start < strtotime($booked['jam_selesai']) &&
                $end > strtotime($booked['jam_mulai'])
            ) {
                return true;
            }
        }

        return false;
    }

    public function calculatePrice()
    {
        $total = 0;

        foreach ($this->selectedTimeSlots as $slotKey) {
            // $slotKey formatnya: "07:00-08:00"
            // Kita butuh jam mulainya saja: "07:00"
            $jamMulai = explode('-', $slotKey)[0];

            // Panggil fungsi sakti di Model Lapangan
            // Ini akan otomatis ngecek: Apakah weekend? Apakah malam?
            $hargaSlot = $this->lapangan->getHargaDinamis($this->selectedDate, $jamMulai);

            $total += $hargaSlot;
        }

        $this->totalPrice = $total;
    }

    // Submit
    public function submitBooking()
    {
        $this->validate([
            'selectedDate' => 'required|date',
            'selectedTimeSlots' => 'required|array|min:1', // Validasi array
        ]);

        $this->calculatePrice();
        if ($this->totalPrice <= 0) {
            $this->dispatch('show-error', ['message' => 'Harga tidak valid']);
            return;
        }

        $customOrderId = 'BOOK-' . now()->format('Ymd') . '-' . strtoupper(Str::random(4));

        try {
            DB::beginTransaction();

            // Siapkan Item Details untuk Midtrans
            $itemDetails = [];

            // LOOPING SLOT YANG DIPILIH
            foreach ($this->selectedTimeSlots as $slotKey) {

                // 1. Ambil Jam Mulai & Selesai dari Slot Key
                // Contoh slotKey: "15:00-16:00"
                $jamPecah = explode('-', $slotKey);
                $jamMulai = $jamPecah[0];   // "15:00"
                $jamSelesai = $jamPecah[1]; // "16:00"

                // 2. HITUNG HARGA DINAMIS (REAL TIME)
                $hargaFix = $this->lapangan->getHargaDinamis($this->selectedDate, $jamMulai);

                // 3. Cek Ketersediaan (Pencegahan Double Booking)
                $isBooked = Booking::where('lapangan_id', $this->lapangan->id)
                    ->where('tanggal', $this->selectedDate)
                    ->where('jam_mulai', $this->selectedDate . ' ' . $jamMulai)
                    ->where('status', '!=', 'cancelled') // Abaikan yang sudah cancel
                    ->exists();

                if ($isBooked) {
                    throw new \Exception("Jam $jamMulai sudah dipesan orang lain.");
                }

                // 4. SIMPAN KE DATABASE
                $booking = Booking::create([
                    'lapangan_id' => $this->lapangan->id,
                    'user_id' => Auth::id(),
                    'tanggal' => $this->selectedDate,
                    'jam_mulai' => $this->selectedDate . ' ' . $jamMulai,
                    'jam_selesai' => $this->selectedDate . ' ' . $jamSelesai,
                    'status' => 'pending',
                    'total_price' => $hargaFix,
                    'order_id' => $customOrderId,
                ]);

                // 5. UPDATE ITEM DETAILS MIDTRANS
                $itemDetails[] = [
                    'id' => 'SLOT-' . $booking->id,
                    'price' => (int) $hargaFix,
                    'quantity' => 1,
                    'name' => "Sewa Lapangan ($jamMulai - $jamSelesai)"
                ];
            }

            // --- KONFIGURASI MIDTRANS ---
            Config::$serverKey = config('midtrans.server_key');
            Config::$isProduction = config('midtrans.is_production');
            Config::$isSanitized = true;
            Config::$is3ds = true;

            $params = [
                'transaction_details' => [
                    'order_id' => $customOrderId, // Gunakan ID Gabungan
                    'gross_amount' => (int) $this->totalPrice, // Total Semua
                ],
                'customer_details' => [
                    'first_name' => Auth::user()->name,
                    'email' => Auth::user()->email,
                    'phone' => Auth::user()->nomor_telepon ?? '',
                ],
                'item_details' => $itemDetails, // List semua jam
                'expiry' => [
                    'start_time' => date("Y-m-d H:i:s O"),
                    'unit' => 'minute',
                    'duration' => 60
                ],
            ];

            $snapToken = Snap::getSnapToken($params);

            // Update Snap Token ke SEMUA booking dengan order_id ini
            Booking::where('order_id', $customOrderId)->update(['snap_token' => $snapToken]);

            DB::commit();

            try {
                $this->sendWhatsAppNotification(
                    $customOrderId,
                    $this->totalPrice,
                    $itemDetails,
                    $this->selectedDate,
                    $this->lapangan->nama
                );
            } catch (\Exception $eWa) {
                Log::error('Gagal kirim WA: ' . $eWa->getMessage());
            }

            // Kirim ke Frontend (Note: booking_id kita ganti kirim customOrderId)
            $this->dispatch('process-payment', [
                'token' => $snapToken,
                'order_id' => $customOrderId // Ubah nama key jadi order_id biar tidak bingung
            ]);

            // Reset
            $this->reset(['selectedTimeSlots', 'totalPrice']);
            $this->updateAvailableTimeSlots();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error Booking: ' . $e->getMessage());
            $this->dispatch('show-error', ['message' => $e->getMessage()]);
        }
    }


    protected function sendWhatsAppNotification($orderId, $totalAmount, $items, $tanggalBooking, $namaLapangan)
    {
        $user = Auth::user();
        $phone = $user->nomor_telepon;

        if (empty($phone)) return; // Stop jika tidak ada nomor

        $phone = ltrim($phone, '+');
        if (substr($phone, 0, 1) === '0') {
            $phone = '62' . substr($phone, 1);
        }

        $listJadwal = "";
        foreach ($items as $item) {
        $jamSaja = str_replace(['Sewa Lapangan (', ')'], '', $item['name']);
        
        $listJadwal .= "• " . $jamSaja . " WIB\n";
    }

        try {
            $tanggalFormatted = \Carbon\Carbon::parse($tanggalBooking)->translatedFormat('d F Y');
        } catch (\Exception $e) {
            $tanggalFormatted = $tanggalBooking; // Fallback jika gagal parse
        }

        $hargaFormatted = number_format($totalAmount, 0, ',', '.');

        $message = "*Halo {$user->name},* \n\n";
        $message .= "Terima kasih telah melakukan booking di Arena Futsal Kraksaan.\n\n";
        $message .= "*Detail Pesanan:*\n";
        $message .= "No. Order: *$orderId*\n";
        $message .= "Tanggal Main: $tanggalFormatted\n";
        $message .= "Jam Booking:\n$listJadwal\n";
        $message .= "Total Tagihan: *Rp $hargaFormatted*\n\n";
        $message .= "Status: *Menunggu Pembayaran*\n";
        $message .= "Silakan selesaikan pembayaran melalui popup di website atau cek menu Riwayat Booking.\n\n";
        $message .= "_Harap abaikan pesan ini jika Anda sudah membayar._";

        try {
            $response = Http::withHeaders([
                'Authorization' => env('FONNTE_API_TOKEN'), // Atau config('services.fonnte.token')
            ])->post('https://api.fonnte.com/send', [
                'target' => $phone,
                'message' => $message,
                'countryCode' => '62',
            ]);

            if ($response->successful()) {
                Log::info("WA Terkirim ke $phone untuk Order $orderId");
            } else {
                Log::warning("Gagal kirim WA Fonnte: " . $response->body());
            }
        } catch (\Exception $e) {
            Log::error("Error HTTP Client WA: " . $e->getMessage());
        }
    }

    public function cancelBooking($customOrderId)
    {
        // 1. Cek apakah Order ID ini ada di Database (Tanpa syarat lain)
        $cekDatabase = Booking::where('order_id', $customOrderId)->first();

        if (!$cekDatabase) {
            Log::error("Gagal Cancel: Order ID $customOrderId TIDAK DITEMUKAN sama sekali di database.");
            $this->dispatch('show-error', ['message' => 'Data booking hilang.']);
            return;
        }

        // 2. Jika ada, mari kita lihat detailnya di Log
        Log::info("Cek Booking $customOrderId:");
        Log::info("- Status di DB: " . $cekDatabase->status); // Cek statusnya apa?
        Log::info("- User ID di DB: " . $cekDatabase->user_id);
        Log::info("- User ID Login: " . Auth::id());
        // Cari semua booking dengan order_id tersebut
        $bookings = Booking::where('order_id', $customOrderId)
            ->where('user_id', Auth::id())
            ->where('status', 'pending')
            ->get();

        if ($bookings->isNotEmpty()) {
            // Update semua jadi cancelled
            Booking::where('order_id', $customOrderId)->update(['status' => 'cancelled']);

            $this->reset(['selectedTimeSlots', 'totalPrice', 'snapToken']);
            $this->updateAvailableTimeSlots();

            $this->dispatch('show-success', ['message' => 'Booking berhasil dibatalkan.']);
        } else {
            $this->dispatch('show-error', ['message' => 'Data tidak ditemukan.']);
        }
    }

    public function render()
    {
        return view('livewire.booking-form');
    }
}
