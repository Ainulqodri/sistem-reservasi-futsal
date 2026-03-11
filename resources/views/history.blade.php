@extends('layouts.app') {{-- Sesuaikan dengan nama file layout utama Anda --}}

{{-- BAGIAN 1: Header (Judul di atas Background Gambar) --}}
@section('header')
    <div class="mt-10 text-center text-white">
        <h1 class="text-4xl font-bold mb-2 text-shadow-sm shadow-black/50">Riwayat Booking</h1>
        <p class="text-lg opacity-90 text-shadow-sm shadow-black/50">
            Pantau status jadwal permainan Anda
        </p>
    </div>
@endsection

{{-- BAGIAN 2: Content (Memanggil Komponen Livewire) --}}
@section('content')
    {{-- Ini sintaks untuk memanggil Livewire di dalam view biasa --}}
    <livewire:booking-history />
@endsection