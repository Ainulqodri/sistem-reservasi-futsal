<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Arena Futsal Kraksaan')</title>
    <meta name="description" content="@yield('meta_description', 'Booking lapangan futsal di Kraksaan, Probolinggo. Rumput sintetis berkualitas, fasilitas lengkap, parkir luas. Cek jadwal dan booking online sekarang!')">
    <meta name="keywords" content="futsal kraksaan, sewa lapangan futsal probolinggo, arena futsal, booking futsal online, lapangan rumput sintetis">
    <meta property="og:title" content="Arena Futsal Kraksaan - Booking Online">
    <meta property="og:description" content="Main futsal jadi lebih mudah. Cek jadwal kosong dan booking langsung dari HP.">
    <meta property="og:image" content="{{ secure_asset('images/logo.png') }}">
    <meta property="og:url" content="{{ url()->current() }}">
    @livewireStyles
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://unpkg.com/akar-icons-fonts"></script>
    {{-- <link rel="stylesheet" href="https://unpkg.com/akar-icons-fonts@latest/build/akar-icons.css"> --}}
</head>

<body class="bg-gray-100">
    <header style="background-image: url({{ asset('images/header.png') }});"
        class="{{ request()->is('/') ? 'min-h-screen' : 'min-h-[500px]' }} bg-center bg-cover">
        <div class="max-w-6xl mx-auto py-10 px-4">
            <nav class="bg-white md:rounded-xl py-4 px-6 relative" x-data="{ open: false }">
                <div class="flex items-center justify-between">
                    <a href="{{ route('home') }}" class="text-base md:text-2xl font-bold text-orange-600">
                        <span class="text-shadow-sm text-shadow-yellow-400">Arena</span>
                        <span class="text-yellow-400 text-shadow-sm text-shadow-orange-600 ms-(-5)">Futsal</span>
                    </a>
                    <button @click="open = !open" class="md:hidden p-2 rounded-md hover:bg-gray-100 focus:outline-none hover:cursor-pointer">
                        <svg x-show="!open" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                        <svg x-show="open" x-cloak class="w-6 h-6" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>

                    <div :class="{ 'block': open, 'hidden': !open }"
                        class="hidden absolute top-full left-0 w-full bg-white shadow-lg rounded-b-xl z-50 p-4 md:p-0 md:static md:bg-transparent md:shadow-none md:flex md:w-auto md:items-center gap-4">

                        <a href="#galeri"
                            class="block mb-2 md:mb-0 py-2.5 px-4 md:px-6 rounded-xl text-center font-semibold transition hover:bg-primary/90 hover:text-white {{ request()->is(url('/') . '#galeri') ? 'bg-primary text-white' : 'bg-gray-100 text-black md:bg-white' }}">
                            Explore
                        </a>

                        @guest
                            <div class="flex flex-col md:flex-row gap-2 items-center">
                                <a href="{{ route('login') }}"
                                    class="w-full md:w-auto text-center px-4 py-2 font-semibold rounded-md text-black hover:bg-gray-200">
                                    Login
                                </a>
                                <span class="hidden md:block border-l border-black h-6"></span>
                                <a href="{{ route('register') }}"
                                    class="w-full md:w-auto text-center px-4 py-2 font-semibold rounded-md text-black hover:bg-gray-200">
                                    Registrasi
                                </a>
                            </div>
                        @endguest

                        @auth
                            @if (!Auth::user()->is_admin)
                                <a href="{{ url('/') }}#pilih-lapangan"
                                    class="block w-full md:w-auto text-center py-2.5 px-6 rounded-xl bg-primary text-white font-semibold transition hover:bg-primary/90 mb-2 md:mb-0">
                                    Booking
                                </a>
                            @endif

                            <div class="relative w-full md:w-auto" x-data="{ dropdownOpen: false }">
                                <button @click="dropdownOpen = !dropdownOpen" @click.away="dropdownOpen = false"
                                    class="flex items-center justify-between w-full md:w-auto cursor-pointer gap-2 px-4 py-2 bg-gray-200 rounded-md hover:bg-gray-300">
                                    <div class="flex items-center gap-2 overflow-hidden">
                                        @if (Auth::user()->is_admin)
                                            <span
                                                class="text-xs font-bold text-white bg-red-600 px-2 py-1 rounded flex-shrink-0">ADMIN</span>
                                        @endif
                                        <span class="truncate">{{ Auth::user()->email }}</span>
                                    </div>
                                    <svg class="w-4 h-4 transition-transform duration-200"
                                        :class="{ 'rotate-180': dropdownOpen }" fill="none" stroke="currentColor"
                                        stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>

                                <div x-show="dropdownOpen" x-transition
                                    class="mt-2 w-full md:absolute md:right-0 md:w-48 bg-white border rounded-md shadow-md py-2 z-50">

                                    @if (Auth::user()->is_admin)
                                        <a href="/admin" class="block px-4 py-2 text-red-600 font-bold hover:bg-gray-100">
                                            Ke Dashboard Admin
                                        </a>
                                    @else
                                        <a href="{{ route('booking.history') }}"
                                            class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                            History Booking
                                        </a>
                                    @endif

                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit"
                                            class="w-full cursor-pointer text-left px-4 py-2 text-red-600 hover:bg-gray-100">
                                            Logout
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endauth

                    </div>
                </div>
            </nav>
            @yield('header')
        </div>
    </header>

    @yield('content')

    <footer class="py-6 bg-[#101C1C]">
        <div class="max-w-6xl mx-auto px-4">
            <div class="flex items-center justify-between">
                <p class="text-white text-sm">&copy; Copyright 2025. Futsal</p>
                <div class="flex gap-4 justify-end">
                    <a href="#" target="_blank" class="text-white text-base hover:text-primary transition">
                        <i class="ai-whatsapp-fill"></i>
                    </a>
                    <a href="#" target="_blank" class="text-white text-base hover:text-primary transition">
                        <i class="ai-instagram-fill"></i>
                    </a>
                    <a href="#" target="_blank" class="text-white text-base hover:text-primary transition">
                        <i class="ai-youtube-fill"></i>
                    </a>
                </div>
            </div>
        </div>
    </footer>
    @livewireScripts
    @stack('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const btn = document.getElementById("dropdownButton");
            const menu = document.getElementById("dropdownMenu");
            const container = document.getElementById("userDropdown");

            // Toggle dropdown
            btn.addEventListener("click", function(e) {
                e.stopPropagation();
                menu.classList.toggle("hidden");
            });

            // Klik di luar → tutup dropdown
            document.addEventListener("click", function(event) {
                if (!container.contains(event.target)) {
                    menu.classList.add("hidden");
                }
            });
        });
    </script>
</body>

</html>
