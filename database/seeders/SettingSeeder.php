<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            [
                'key' => 'jam_buka',
                'value' => '07:00',
                'description' => 'Jam mulai operasional lapangan',
            ],
            [
                'key' => 'jam_tutup',
                'value' => '24:00',
                'description' => 'Jam tutup operasional lapangan',
            ],
            [
                'key' => 'libur_idul_fitri',
                'value' => '2026-05-20', // Contoh tanggal
                'description' => 'Tanggal merah Idul Fitri (Lapangan Tutup)',
            ],
            [
                'key' => 'libur_idul_adha',
                'value' => '2026-07-28', // Contoh tanggal
                'description' => 'Tanggal merah Idul Adha (Lapangan Tutup)',
            ],
        ];

        foreach ($settings as $setting) {
            // updateOrCreate: Jika key sudah ada, update nilainya. Jika belum, buat baru.
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
