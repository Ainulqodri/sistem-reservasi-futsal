<div class="max-w-6xl mx-auto px-4 -mt-10 pb-20 relative z-10">

    {{-- Statistik Singkat --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center gap-4">
            <div class="p-3 bg-blue-50 text-blue-600 rounded-lg">
                <i class="ai-schedule text-2xl"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500">Total Booking</p>
                <p class="text-xl font-bold">{{ $bookings->total() }}</p>
            </div>
        </div>
    </div>

    {{-- Tabel History --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        @if ($bookings->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr
                            class="bg-gray-50 border-b border-gray-200 text-xs uppercase text-gray-500 font-semibold tracking-wider">
                            <th class="px-6 py-4">No</th>
                            <th class="px-6 py-4">Lapangan</th>
                            <th class="px-6 py-4">Jadwal Main</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4">Total</th>
                            <th class="px-6 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($bookings as $index => $booking)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 text-gray-500">
                                    {{ $bookings->firstItem() + $index }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span
                                            class="font-semibold text-gray-800">{{ $booking->lapangan->title ?? 'Lapangan Dihapus' }}</span>
                                        <span class="text-xs text-gray-500">Booking ID: #{{ $booking->id }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="font-medium text-gray-800">
                                            {{ \Carbon\Carbon::parse($booking->tanggal)->translatedFormat('d F Y') }}
                                        </span>
                                        <span class="text-sm text-gray-500">
                                            {{ \Carbon\Carbon::parse($booking->jam_mulai)->format('H:i') }} -
                                            {{ \Carbon\Carbon::parse($booking->jam_selesai)->format('H:i') }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    @php
                                        $statusClasses = [
                                            'pending' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                                            'confirmed' => 'bg-green-100 text-green-700 border-green-200',
                                            'lunas' => 'bg-green-100 text-green-700 border-green-200',
                                            'cancelled' => 'bg-red-100 text-red-700 border-red-200',
                                            'selesai' => 'bg-gray-100 text-gray-700 border-gray-200',
                                        ];
                                        $currentClass = $statusClasses[$booking->status] ?? 'bg-gray-100 text-gray-600';
                                        $statusLabel = ucfirst($booking->status);
                                        if ($booking->status == 'pending') {
                                            $statusLabel = 'Menunggu Konfirmasi';
                                        }
                                    @endphp

                                    <span
                                        class="px-3 py-1 rounded-full text-xs font-semibold border {{ $currentClass }}">
                                        {{ $statusLabel }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 font-medium text-gray-800">
                                    Rp {{ number_format($booking->total_price ?? 0, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if ($booking->status === 'pending')
                                        <div class="flex flex-col gap-2 items-center">
                                            {{-- TOMBOL BAYAR --}}
                                            @if ($booking->snap_token)
                                                <button
                                                    onclick="window.snap.pay('{{ $booking->snap_token }}', {
                        onSuccess: function(result){ window.location.reload(); },
                        onPending: function(result){ window.location.reload(); },
                        onError: function(result){ location.reload(); },
                        onClose: function(){ location.reload(); }
                    })"
                                                    class="px-4 py-1.5 rounded-lg bg-primary text-white text-xs font-semibold hover:bg-primary/90 transition shadow-sm w-full">
                                                    Bayar Sekarang
                                                </button>
                                            @endif

                                            {{-- TOMBOL BATALKAN --}}
                                            <button type="button" x-data
                                                @click="
                    Swal.fire({
                        title: 'Batalkan Booking?',
                        text: 'Slot lapangan akan dilepas kembali.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Ya, Batalkan!',
                        cancelButtonText: 'Tidak'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $wire.cancelBooking('{{ $booking->order_id }}')
                        }
                    })
                "
                                                class="text-xs text-red-500 hover:text-red-700 hover:underline transition">
                                                Batalkan
                                            </button>
                                        </div>
                                    @elseif($booking->status === 'cancelled')
                                        {{-- Konsisten dengan warna Merah/Gray --}}
                                        <div class="flex flex-col items-center">
                                            <span class="text-red-500 text-xs font-medium">Dibatalkan</span>
                                            <span class="text-[10px] text-gray-400">(Slot Hangus)</span>
                                        </div>
                                    @elseif($booking->status === 'confirmed' || $booking->status === 'lunas')
                                        {{-- Konsisten dengan warna Hijau --}}
                                        <div class="flex flex-col items-center">
                                            <span
                                                class="text-white bg-green-700 p-1 text-center rounded-md text-xs font-bold flex items-center gap-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                    viewBox="0 0 24 24" fill="currentColor" stroke="none">
                                                    <path
                                                        d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" />
                                                </svg>LUNAS
                                            </span>
                                        </div>
                                    @elseif($booking->status === 'selesai')
                                        <span class="text-gray-500 text-xs font-medium">Selesai</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 border-t border-gray-200">
                {{ $bookings->links() }}
            </div>
        @else
            {{-- Empty State --}}
            <div class="text-center py-16 px-6">
                <div class="bg-gray-50 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="ai-calendar-alert text-3xl text-gray-400"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-800 mb-1">Belum ada riwayat booking</h3>
                <p class="text-gray-500 mb-6">Anda belum pernah melakukan reservasi lapangan.</p>
                <a href="{{ url('/') }}#pilih-lapangan"
                    class="inline-block py-2.5 px-6 rounded-xl bg-primary text-white font-semibold transition hover:bg-primary/90">
                    Booking Sekarang
                </a>
            </div>
        @endif
    </div>
</div>

{{-- Script SweetAlert khusus komponen ini --}}
@push('scripts')
    {{-- 1. LOAD SCRIPT MIDTRANS (WAJIB ADA) --}}
    {{-- Pastikan config client_key sudah benar di .env --}}
    <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}">
    </script>

    {{-- 2. Script SweetAlert --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Event listener untuk notifikasi sukses/error
        Livewire.on('show-success', payload => {
            let message = Array.isArray(payload) ? payload[0].message : payload.message;
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: message,
                timer: 2000,
                showConfirmButton: false
            });
        });

        Livewire.on('show-error', payload => {
            let message = Array.isArray(payload) ? payload[0].message : payload.message;
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: message
            });
        });
    </script>
@endpush
