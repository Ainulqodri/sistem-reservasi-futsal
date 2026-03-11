<div class="max-w-6xl mx-auto px-4" id="cek-jadwal">
    
    <div class="text-center mb-6">
        <h2 class="text-3xl font-bold text-gray-800">Cek Ketersediaan Lapangan</h2>
        <p class="text-gray-600 mt-2">Pilih lapangan dan tanggal untuk melihat jadwal kosong.</p>
    </div>

    <div class="flex flex-col md:flex-row justify-center gap-4 mb-8">
        
        <div class="flex items-center gap-3 bg-white p-3 rounded-xl shadow-sm border">
            <label class="font-semibold text-gray-700 whitespace-nowrap">Lapangan:</label>
            <select wire:model.live="selectedLapangan" 
                    class="border-gray-300 rounded-lg focus:ring-primary focus:border-primary w-full md:w-48">
                @foreach($semuaLapangan as $lap)
                    <option value="{{ $lap->id }}">{{ $lap->title }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex items-center gap-3 bg-white p-3 rounded-xl shadow-sm border">
            <label class="font-semibold text-gray-700 whitespace-nowrap">Tanggal:</label>
            <input type="date" 
                   wire:model.live="selectedDate" 
                   class="border-gray-300 rounded-lg focus:ring-primary focus:border-primary"
                   min="{{ date('Y-m-d') }}">
        </div>

    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
        @foreach($jamOperasional as $jam)
            @php
                // 1. Cek Status Booking (Database)
                $isBooked = in_array($jam, $bookedSlots);
                
                // 2. Format Jam Selesai
                $jamSelesai = \Carbon\Carbon::parse($jam)->addHour()->format('H:i');

                // 3. LOGIKA BARU: Cek Apakah Jam Sudah Lewat?
                // Gabungkan Tanggal yang dipilih user + Jam slot ini
                $waktuSlot = \Carbon\Carbon::parse($selectedDate . ' ' . $jam);
                $waktuSekarang = \Carbon\Carbon::now();
                
                // Jika waktu slot LEBIH KECIL dari waktu sekarang, berarti sudah lewat
                $isPassed = $waktuSlot->lt($waktuSekarang);
            @endphp

            {{-- TENTUKAN WARNA CARD --}}
            <div class="relative rounded-xl border p-4 text-center transition duration-300
                @if($isBooked)
                    {{-- MERAH: Jika sudah dibooking orang --}}
                    bg-red-50 border-red-200 text-red-600 cursor-not-allowed opacity-80
                @elseif($isPassed)
                    {{-- ABU-ABU: Jika jam sudah lewat (Exp: Jam 08.00 padahal skrng jam 12.00) --}}
                    bg-gray-100 border-gray-200 text-gray-400 cursor-not-allowed
                @else
                    {{-- HIJAU: Jika Masih Kosong & Belum Lewat --}}
                    bg-green-50 border-green-200 text-green-700 hover:shadow-md hover:scale-105 cursor-pointer
                @endif
            ">
                
                <p class="text-lg font-bold">{{ $jam }} - {{ $jamSelesai }}</p>
                
                <div class="mt-2 text-xs font-bold uppercase tracking-wider flex justify-center items-center gap-1">
                    @if($isBooked)
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        Terisi
                    @elseif($isPassed)
                        {{-- Icon Jam Pasir / X untuk yang lewat --}}
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Lewat
                    @else
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Tersedia
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-8 text-center">
        @guest
            <p class="text-gray-600 mb-2">Jadwal di atas untuk <strong>{{ $semuaLapangan->find($selectedLapangan)->title ?? 'Lapangan Ini' }}</strong></p>
            <a href="{{ route('login') }}" class="inline-block px-6 py-2 bg-primary text-white font-bold rounded-lg hover:bg-primary/90 transition shadow-lg shadow-primary/30">
                Login untuk Booking
            </a>
        @else
            <p class="text-gray-600 mb-2">Anda melihat jadwal: <strong>{{ $semuaLapangan->find($selectedLapangan)->title ?? 'Lapangan Ini' }}</strong></p>
            <a href="{{ url('/') }}#pilih-lapangan" class="inline-block px-6 py-2 bg-primary text-white font-bold rounded-lg hover:bg-primary/90 transition shadow-lg shadow-primary/30">
                Booking Sekarang
            </a>
        @endguest
    </div>

</div>