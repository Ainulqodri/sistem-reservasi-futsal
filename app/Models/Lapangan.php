<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lapangan extends Model
{
    protected $table = 'lapangan';

    protected $fillable = [
        'title',
        'category',
        'description',
        'price',
        'images',
        'status',
        'price_weekday_night', 
        'price_weekend_day',
        'price_weekend_night'
    ];

    protected $casts = [
        'images' => 'array',
        'status' => 'boolean'
    ];
    // app/Models/Lapangan.php

    public function getHargaDinamis($tanggal, $jamMulai)
    {
        $date = \Carbon\Carbon::parse($tanggal);
        $isWeekend = $date->isWeekend(); // Sabtu/Minggu
        $jam = (int) substr($jamMulai, 0, 2);
        $isNight = $jam >= 15; // Batas jam 15:00 sore

        // Default: Ambil harga dasar (Pagi Weekday)
        $hargaFinal = $this->price;

        // --- LOGIKA MURNI DARI DATABASE ---

        if ($isWeekend) {
            if ($isNight) {
                // Sabtu-Minggu Malam
                // Ambil kolom price_weekend_night. Jika kosong, pakai harga dasar.
                $hargaFinal = $this->price_weekend_night ?? $this->price;
            } else {
                // Sabtu-Minggu Pagi
                $hargaFinal = $this->price_weekend_day ?? $this->price;
            }
        } else {
            // Senin-Jumat (Weekday)
            if ($isNight) {
                // Senin-Jumat Malam
                $hargaFinal = $this->price_weekday_night ?? $this->price;
            } else {
                // Senin-Jumat Pagi (Tetap harga dasar)
                $hargaFinal = $this->price;
            }
        }

        return $hargaFinal;
    }

    // Relasi ke bookings
}
