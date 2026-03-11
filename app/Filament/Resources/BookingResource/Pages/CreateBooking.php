<?php

namespace App\Filament\Resources\BookingResource\Pages;

use App\Filament\Resources\BookingResource;
use App\Models\Booking;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CreateBooking extends CreateRecord
{
    protected static string $resource = BookingResource::class;
    protected static bool $canCreateAnother = false;

    protected function getRedirectUrl(): string
    {
        // Mengarahkan kembali ke halaman Index (Tabel)
        return $this->getResource()::getUrl('index');
    }

    protected function handleRecordCreation(array $data): Model
    {
        // $data['jam_mulai'] isinya array ["19:00", "20:00"]
        $jamList = $data['jam_mulai'];

        // Kita butuh 1 record utama untuk dikembalikan ke Filament (agar tidak error)
        $firstBooking = null;

        DB::transaction(function () use ($data, $jamList, &$firstBooking) {
            foreach ($jamList as $jam) {

                // 1. Siapkan data per jam
                $singleData = $data;
                $singleData['jam_mulai'] = $singleData['tanggal'] . ' ' . $jam;
                $singleData['jam_selesai'] = $singleData['tanggal'] . ' ' . \Carbon\Carbon::parse($jam)->addHour()->format('H:i');

                // Hitung harga per jam (karena total_price di form adalah total semua)
                // Kita hitung ulang biar akurat masuk DB per barisnya
                $lapangan = \App\Models\Lapangan::find($data['lapangan_id']);
                $singleData['total_price'] = $lapangan->getHargaDinamis($data['tanggal'], $jam);

                // Pastikan user_id terisi
                if (empty($singleData['user_id'])) {
                    $singleData['user_id'] = Auth::id();
                }

                // 2. Simpan ke Database
                $booking = Booking::create($singleData);

                // Simpan booking pertama untuk return value
                if (!$firstBooking) {
                    $firstBooking = $booking;
                }
            }
        });

        if (!$firstBooking) {
            throw new \Exception("Gagal membuat booking: Tidak ada slot waktu yang dipilih.");
        }

        return $firstBooking;
    }
}
