@extends('layouts.app')

@section('header')
    <div class="mt-42">
        <h1 class="text-center text-6xl text-white font-bold">
            Life is like Soccer <br class="hidden md:block">
            You need Goals
        </h1>
        <p class="text-center text-white mt-6 w-full md:w-6/12 mx-auto">
            Join us for a game of soccer and make some goals.
            Whether you're a pro or a beginner, there's a game for everyone.
        </p>
        <div class="flex justify-center gap-4 mt-8">
            <a href=""
                class="py-2.5 px-6 rounded-xl bg-white text-black font-semibold transition hover:bg-primary/90 hover:text-white">
                Explore
            </a>
            @guest
                <a href="{{ route('login') }}"
                    class="py-2.5 px-6 rounded-xl bg-primary text-white font-semibold transition hover:bg-primary/90">
                    Booking
                </a>
            @endguest

            @auth
                <a href="#pilih-lapangan"
                    class="py-2.5 px-6 rounded-xl bg-primary text-white font-semibold transition hover:bg-primary/90">
                    Booking
                </a>
            @endauth
        </div>
    </div>
@endsection

@section('content')
    <section class="bg-gray-50 py-10 border-b">
        @livewire('cek-jadwal')
    </section>
    @auth
        @if (Auth::user()->is_admin == false)
            <section class="pt-10 md:pt-24" id="pilih-lapangan">
                <div class="max-w-6xl mx-auto px-4">
                    <span
                        class="mx-auto w-max py-2 px-6 rounded-full bg-primary text-white text-xs font-semibold flex items-center gap-2">
                        <i class="ai-align-left"></i>
                        Pilih Lapangan
                    </span>
                    <h2 class="text-center text-2xl md:text-4xl font-bold mt-3">
                        Pilih Lapangan Futsal
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-10">
                        @foreach ($lapangan as $item)
                            <a href="{{ route('lapangan.detail', $item->id) }}"
                                class="bg-white border border-transparent rounded-2xl transition hover:border-primary">
                                <img src="{{ asset('storage/' . (is_array($item->images) && count($item->images) > 0 ? $item->images[0] : 'default-lapangan.png')) }}"
                                    class="w-full h-60 rounded-t-2xl object-cover" alt="">
                                <div class="p-6">
                                    <span
                                        class="text-xs w-max py-2 px-6 rounded-full {{ $item->category == 'unggulan' ? 'bg-tertiary' : 'bg-primary' }} text-white font-semibold flex items-center gap-2">
                                        <i class="ai-star"></i>
                                        {{ $item->category }}
                                    </span>
                                    <h4 class="text-xl font-semibold mt-2">{{ $item->title }}</h4>
                                    <p class="text-gray-500">
                                        Mulai dari Rp. {{ number_format($item->price) }} <span class="text-xs">/jam</span>
                                    </p>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif
    @endauth
    <section class="pt-10 md:pt-24">
        <div class="max-w-6xl mx-auto px-4">
            <span
                class="mx-auto w-max py-2 px-6 rounded-full bg-primary text-white text-xs font-semibold flex items-center gap-2">
                <i class="ai-info"></i>
                How To
            </span>
            <h2 class="text-center text-2xl md:text-4xl font-bold mt-3">
                Cara Booking
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mt-10">
                <div class="bg-white rounded-2xl p-14 text-center">
                    <span class="mx-auto p-6 w-12 h-12 bg-primary text-white rounded-full flex items-center justify-center">
                        1
                    </span>
                    <h5 class="font-semibold text-2xl mt-4">
                        Register & Login
                    </h5>
                </div>
                <div class="bg-white rounded-2xl p-14 text-center">
                    <span class="mx-auto p-6 w-12 h-12 bg-primary text-white rounded-full flex items-center justify-center">
                        2
                    </span>
                    <h5 class="font-semibold text-2xl mt-4">
                        Pilih Lapangan
                    </h5>
                </div>
                <div class="bg-white rounded-2xl p-14 text-center">
                    <span class="mx-auto p-6 w-12 h-12 bg-primary text-white rounded-full flex items-center justify-center">
                        3
                    </span>
                    <h5 class="font-semibold text-2xl mt-4">
                        Pilih Tanggal & Jam
                    </h5>
                </div>
                <div class="bg-white rounded-2xl p-14 text-center">
                    <span class="mx-auto p-6 w-12 h-12 bg-primary text-white rounded-full flex items-center justify-center">
                        4
                    </span>
                    <h5 class="font-semibold text-2xl mt-4">
                        Bayar
                    </h5>
                </div>
            </div>
        </div>
    </section>
    <section class="pt-10 md:pt-24" id="galeri">
        <div class="max-w-6xl mx-auto px-4">
            <span
                class="mx-auto w-max py-2 px-6 rounded-full bg-primary text-white text-xs font-semibold flex items-center gap-2">
                <i class="ai-image"></i>
                Galeri
            </span>
            <h2 class="text-center text-2xl md:text-4xl font-bold mt-3">
                Galeri Futsal
            </h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-10">
                <img src="images/galeri-1.jpg" class="w-full h-44 object-cover rounded-xl" alt="">
                <img src="images/galeri-2.jpg" class="w-full h-44 object-cover rounded-xl" alt="">
                <img src="images/galeri-3.jpg" class="w-full h-44 object-cover rounded-xl" alt="">
                <img src="images/galeri-4.jpg" class="w-full h-44 object-cover rounded-xl" alt="">
                <img src="images/galeri-5.jpg" class="w-full h-44 object-cover rounded-xl" alt="">
                <img src="images/galeri-6.jpg" class="w-full h-44 object-cover rounded-xl" alt="">
                <img src="images/header.png" class="w-full h-44 object-cover rounded-xl" alt="">
                <img src="images/lapangan-1.png" class="w-full h-44 object-cover rounded-xl" alt="">
            </div>
        </div>
    </section>
    <section class="py-10 md:py-24">
        <div class="max-w-6xl mx-auto px-4">
            <span
                class="mx-auto w-max py-2 px-6 rounded-full bg-primary text-white text-xs font-semibold flex items-center gap-2">
                <i class="ai-phone"></i>
                Contact Us
            </span>
            <h2 class="text-center text-2xl md:text-4xl font-bold mt-3">
                Hubungi Kami
            </h2>

            <div class="flex flex-col md:flex-row gap-6 mt-10">

                <div class="w-full md:w-4/12">
                    <div class="rounded-xl p-6 bg-white shadow-sm border border-gray-100">
                        <div class="flex items-center gap-4 mb-4">
                            <div
                                class="rounded-full bg-primary text-white p-2 w-10 h-10 flex items-center justify-center flex-shrink-0">
                                <i class="ai-whatsapp-fill"></i>
                            </div>
                            <div class="flex flex-col">
                                <span class="text-sm font-semibold">Whatsapp</span>
                                <span class="text-xs">+62 812-3456-7890</span>
                            </div>
                        </div>

                        <div class="flex items-center gap-4 mb-4">
                            <div
                                class="rounded-full bg-primary text-white p-2 w-10 h-10 flex items-center justify-center flex-shrink-0">
                                <i class="ai-envelope"></i>
                            </div>
                            <div class="flex flex-col">
                                <span class="text-sm font-semibold">Email</span>
                                <span class="text-xs break-all">arenafutsalkraksaan@gmail.com</span>
                            </div>
                        </div>

                        <div class="flex items-start gap-4">
                            <div
                                class="rounded-full bg-primary text-white p-2 w-10 h-10 flex items-center justify-center flex-shrink-0">
                                <i class="ai-map"></i>
                            </div>
                            <div class="flex flex-col">
                                <span class="text-sm font-semibold">Alamat</span>
                                <span class="text-xs leading-relaxed">Patokan, Kec. Kraksaan, Kabupaten Probolinggo</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="w-full md:w-8/12 rounded-xl bg-white overflow-hidden shadow-sm">
                    <iframe
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d988.3192659371098!2d113.41729154661816!3d-7.760412898709924!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2dd700509e1748f1%3A0xccc9d1c864369843!2sLapangan%20Arena%20Futsal!5e0!3m2!1sen!2sid!4v1763295436235!5m2!1sen!2sid"
                        class="w-full h-[300px] md:h-[450px] rounded-xl" style="border:0;" allowfullscreen=""
                        loading="lazy" referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>
            </div>
        </div>
    </section>
@endsection
