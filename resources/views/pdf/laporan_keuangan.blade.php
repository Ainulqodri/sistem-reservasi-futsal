<body>
    <h1 style="text-align: center">Laporan Keuangan</h1>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>No. Order</th>
                <th>Nama Customer</th>
                <th>Lapangan</th>
                <th>Harga</th>
            </tr>
        </thead>
        <tbody>
            {{-- 1. INISIALISASI VARIABEL TOTAL --}}
            @php $totalSemua = 0; @endphp

            @foreach($bookings as $index => $row)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ \Carbon\Carbon::parse($row->tanggal)->format('d-m-Y') }}</td>
                <td>{{ $row->order_id }}</td>
                <td>{{ $row->user->name ?? '-' }}</td>
                <td>{{ $row->lapangan->title ?? '-' }}</td>
                <td style="text-align: right;">
                    Rp {{ number_format($row->total_price, 0, ',', '.') }}
                </td>
            </tr>

            {{-- 2. JUMLAHKAN HARGA PER BARIS --}}
            @php $totalSemua += $row->total_price; @endphp
            
            @endforeach

            {{-- 3. BARIS TOTAL PENDAPATAN (DI LUAR LOOP, TAPI MASIH DI DALAM TBODY) --}}
            <tr style="background-color: #f2f2f2;">
                {{-- colspan="5" artinya menggabungkan 5 kolom pertama jadi satu --}}
                {{-- Sesuaikan angkanya dengan jumlah kolom tabel Anda dikurangi 1 --}}
                <td colspan="5" style="text-align: center; font-weight: bold;">
                    TOTAL PENDAPATAN
                </td>
                <td style="text-align: right; font-weight: bold;">
                    Rp {{ number_format($totalSemua, 0, ',', '.') }}
                </td>
            </tr>
        </tbody>
    </table>

    </body>
</html>