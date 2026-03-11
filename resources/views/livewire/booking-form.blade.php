<div>
    {{-- Pesan sukses --}}
    @if (session()->has('success'))
        <div class="alert alert-success mb-4">
            {{ session('success') }}
        </div>
    @endif

    {{-- Pesan error --}}
    @if (session()->has('error'))
        <div class="alert alert-danger mb-4">
            {{ session('error') }}
        </div>
    @endif
    <div class="flex flex-col md:flex-row gap-3">
        <div class="w-full md:w-8/12">
            <span
                class="text-xs w-max py-2 px-6 rounded-full {{ $lapangan->category == 'unggulan' ? 'bg-tertiary' : 'bg-primary' }} text-white font-semibold flex items-center gap-2">
                <i class="ai-star"></i>
                {{ $lapangan->category }}
            </span>
            <h4 class="text-4xl font-semibold mt-2">{{ $lapangan->title }}</h4>
            <hr class="border border-gray-200 my-10">
            <div class="mb-5">
                <p class="text-lg font-medium mb-2">Deskripsi</p>
                <div class="text-gray-700">
                    {!! $lapangan->description !!}
                </div>
            </div>
            <div class="bg-blue-50 p-5 rounded-xl border border-blue-100 mb-6">
                <h5 class="font-bold text-blue-800 mb-3 flex items-center gap-2">
                    <i class="ai-price-tag"></i> Daftar Harga Sewa
                </h5>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Kolom Senin - Jumat --}}
                    <div>
                        <p class="font-semibold text-gray-800 mb-1">Senin - Jumat</p>
                        <ul class="text-sm text-gray-600 space-y-1">
                            <li class="flex justify-between border-b border-blue-200 pb-1">
                                <span>07:00 - 15:00</span>
                                {{-- Ini otomatis mengambil harga dasar lapangan yang sedang dibuka --}}
                                <span class="font-bold">Rp. {{ number_format($lapangan->price) }}</span>
                            </li>
                            <li class="flex justify-between pt-1">
                                <span>15:00 - 24:00</span>
                                <span class="font-bold">
                                    {{-- Mengambil harga malam weekday lapangan tsb --}}
                                    {{ $lapangan->price_weekday_night ? 'Rp. ' . number_format($lapangan->price_weekday_night) : '-' }}
                                </span>
                            </li>
                        </ul>
                    </div>

                    {{-- Kolom Sabtu & Minggu --}}
                    <div>
                        <p class="font-semibold text-gray-800 mb-1">Sabtu & Minggu</p>
                        <ul class="text-sm text-gray-600 space-y-1">
                            <li class="flex justify-between border-b border-blue-200 pb-1">
                                <span>07:00 - 15:00</span>
                                <span class="font-bold">
                                    {{ $lapangan->price_weekend_day ? 'Rp. ' . number_format($lapangan->price_weekend_day) : '-' }}
                                </span>
                            </li>
                            <li class="flex justify-between pt-1">
                                <span>15:00 - 24:00</span>
                                <span class="font-bold">
                                    {{ $lapangan->price_weekend_night ? 'Rp. ' . number_format($lapangan->price_weekend_night) : '-' }}
                                </span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="w-full md:w-4/12">
            <div class="rounded-xl bg-white p-6">
                <p class="mb-0 text-gray-500">Mulai dari</p>
                <p class="mb-5 font-semibold text-lg">
                    Rp. 60,000 <span class="text-xs font-normal">per sesi</span>
                </p>

                <a href="#booking-form"
                    class="py-2.5 px-3 md:px-6 rounded-xl bg-primary text-white font-semibold transition hover:bg-primary/90 w-full block text-center">
                    Pilih Tanggal dan Jam
                </a>
            </div>
        </div>
    </div>
    <hr class="border border-gray-200 my-10">
    <p class="text-lg font-medium mb-5">Pilih Tanggal</p>
    <div class="grid grid-cols-3 md:grid-cols-8 gap-2 mb-5" id="booking-form">
        @foreach ($availableDates as $date)
            <button wire:click="selectDate('{{ $date['date'] }}')"
                class="cursor-pointer border rounded-xl text-center p-6 transition {{ $selectedDate === $date['date'] ? 'bg-primary border-primary text-white' : 'bg-white border border-gray-200 text-black hover:bg-primary hover:border-primary hover:text-white' }}">
                <p class="font-medium">{{ $date['day'] }}</p>
                <p class="font-semibold">{{ $date['formatted'] }}</p>
            </button>
        @endforeach
        <button
            class="cursor-pointer bg-white border border-gray-200 rounded-xl text-center text-black p-6 transition hover:bg-primary hover:border-primary hover:text-white">
            <i class="ai-calendar text-2xl"></i>
        </button>
    </div>

    @if ($selectedDate)
        <p class="text-lg font-medium mb-5">Pilih Jam (Bisa pilih lebih dari satu)</p>
        <div class="grid grid-cols-2 md:grid-cols-6 gap-2 mb-5">
            @foreach ($availableTimeSlots as $slot)
                {{-- KONDISI 1: SUDAH DIBOOKING ORANG --}}
                @if ($slot['is_booked'])
                    <button disabled
                        class="bg-white border border-gray-200 rounded-xl text-center text-gray-400 p-6 transition opacity-50 cursor-not-allowed">
                        <p class="font-normal text-sm">60 Menit</p>
                        <p class="font-semibold">{{ $slot['label'] }}</p>
                        <p class="font-normal text-sm">Booked</p>
                    </button>

                    {{-- KONDISI 2: JAM SUDAH LEWAT (BARU) --}}
                @elseif ($slot['is_passed'])
                    <button disabled
                        class="bg-gray-100 border border-gray-200 rounded-xl text-center text-gray-400 p-6 cursor-not-allowed">
                        <p class="font-normal text-sm">60 Menit</p>
                        <p class="font-semibold">{{ $slot['label'] }}</p>
                        <p class="font-normal text-xs text-red-400 italic">Sudah Lewat</p>
                    </button>

                    {{-- KONDISI 3: TERSEDIA (BISA DIKLIK) --}}
                @else
                    @php
                        $isSelected = in_array($slot['slot_key'], $selectedTimeSlots);
                    @endphp

                    <button wire:click="selectTimeSlot('{{ $slot['slot_key'] }}')"
                        class="relative cursor-pointer rounded-xl text-center p-6 transition 
                {{ $isSelected ? 'bg-primary text-white border-primary' : 'bg-white text-black border border-gray-200 hover:border-primary' }}">

                        <p class="font-normal text-sm">60 Menit</p>
                        <p class="font-semibold">{{ $slot['label'] }}</p>
                        <p class="font-normal text-sm">Rp. {{ number_format($slot['price']) }}</p>

                        @if ($isSelected)
                            <span
                                class="size-7 rounded-full bg-white text-primary absolute top-1 right-1 flex items-center justify-center shadow-sm">
                                <i class="ai-check font-bold"></i>
                            </span>
                        @endif
                    </button>
                @endif
            @endforeach
        </div>
    @endif

    {{-- Tampilkan Total Harga jika ada yang dipilih --}}
    @if (!empty($selectedTimeSlots))
        <div
            class="fixed bottom-0 left-0 w-full bg-white border-t border-gray-200 p-4 shadow-lg md:relative md:border-0 md:shadow-none md:p-0 md:bg-transparent">
            <div
                class="flex flex-col md:flex-row justify-between items-center gap-4 bg-gray-50 p-4 rounded-xl border border-gray-200">
                <div>
                    <p class="text-gray-500 text-sm">Total Pembayaran</p>
                    <p class="text-2xl font-bold text-primary">Rp. {{ number_format($totalPrice) }}</p>
                    <p class="text-xs text-gray-500">{{ count($selectedTimeSlots) }} slot dipilih</p>
                </div>

                <form wire:submit.prevent="submitBooking" class="w-full md:w-auto">
                    <button type="submit" wire:loading.attr="disabled"
                        class="w-full md:w-auto cursor-pointer py-3 px-8 rounded-xl bg-primary text-white font-semibold transition hover:bg-primary/90 flex items-center justify-center gap-2">
                        <span wire:loading.remove wire:target="submitBooking">Bayar Sekarang</span>
                        <span wire:loading wire:target="submitBooking"><i class="animate-spin ai-arrow-clockwise"></i>
                            Processing...</span>
                    </button>
                </form>
            </div>
        </div>
    @endif

</div>
@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}">
    </script>
    <script>
        Livewire.on('show-success', payload => {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: payload.message, // ambil message dari payload
                showConfirmButton: false,
                timer: 2000
            }).then(() => {
                window.location.href = "{{ route('booking.history') }}"
            });
        });

        Livewire.on('show-error', payload => {
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: payload.message, // ambil message dari payload
            });
        });

        Livewire.on('process-payment', (data) => {
            var snapToken = data[0].token;
            var orderId = data[0].order_id;

            // 1. Simpan opsi Snap ke dalam variabel dulu
            var snapOptions = {
                onSuccess: function(result) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Pembayaran Berhasil!',
                        text: 'Terima kasih telah melakukan booking.',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = "{{ route('booking.history') }}";
                    });
                },
                onPending: function(result) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Menunggu Pembayaran',
                        text: 'Silakan selesaikan pembayaran Anda.',
                    }).then(() => {
                        window.location.href = "{{ route('booking.history') }}";
                    });
                },
                onError: function(result) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Pembayaran Gagal',
                        text: 'Terjadi kesalahan saat memproses pembayaran.',
                    });
                },
                onClose: function() {
                    Swal.fire({
                        title: 'Batal Bayar?',
                        text: 'Booking akan otomatis dibatalkan jika Anda keluar.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Batalkan',
                        confirmButtonColor: '#d33',
                        cancelButtonText: 'Lanjutkan Bayar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // PANGGIL METHOD PHP DARI SINI
                            // Ini akan menjalankan function cancelBooking($id) di BookingForm.php
                            @this.call('cancelBooking', orderId);
                        } else {
                            // Munculkan popup lagi
                            window.snap.pay(snapToken, snapOptions);
                        }
                    });
                }
            };

            // 2. Eksekusi Snap pertama kali menggunakan variabel opsi di atas
            window.snap.pay(snapToken, snapOptions);
        });
    </script>
@endpush
