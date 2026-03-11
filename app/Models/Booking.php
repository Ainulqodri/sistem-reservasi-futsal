<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'lapangan_id',
        'user_id',
        'tanggal',
        'jam_mulai',
        'jam_selesai',
        'status',
        'total_price',
        'order_id',
        'snap_token',
    ];

    public function lapangan()
    {
        return $this->belongsTo(Lapangan::class);
    }

    public function getDurationAttribute()
    {
        $mulai = Carbon::parse($this->jam_mulai);
        $selesai = Carbon::parse($this->jam_selesai);

        $durasi = $mulai->diffInHours($selesai);

        return $durasi . 'jam';
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
