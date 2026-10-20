<!-- @extends('layouts.laporan')

    @section('title', $judul ?? 'Laporan')

    @section('content')
    <h2>{{ $judul ?? 'Laporan' }}</h2>
    @if(isset($periode))
        <p><strong>Periode:</strong> {{ $periode }}</p>
    @endif

    <p><strong>Dicetak pada:</strong> {{ now()->translatedFormat('H:i - l - d/m/Y') }}</p>

    <table>
        <thead>
            <tr>
                <th>No.</th>
                <th>Jenis Transaksi</th>
                <th>Atas Nama</th>
                <th>volume Stock (m³)</th>
                <th>Total Keuntungan</th>
                <th>Tanggal</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transaksi as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ ucfirst($item->jenis_transaksi ?? '') }}</td>
                <td>{{ $item->atas_nama ?? '' }}</td>
                <td>{{ number_format((float)($item->total_volume ?? 0), 3, ',', '.') }}</td>
                <td>Rp{{ number_format((float)($item->total_pembayaran ?? 0), 0, ',', '.') }}</td>
                <td>{{ \Carbon\Carbon::parse($item->tanggal_transaksi ?? $item->created_at)->format('d-m-Y H:i') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center">Tidak ada transaksi pada periode ini.</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <th colspan="3" style="text-align:right;">Total</th>
                <th>{{ number_format($total_volume, 3, ',', '.') }}</th>
                <th>Rp{{ number_format($total_pembayaran, 0, ',', '.') }}</th>
                <th></th>
            </tr>
        </tfoot>
    </table>
@endsection -->

<!-- @extends('layouts.laporan')

    @section('title', $judul ?? 'Laporan')

    @section('content')

    <h2>{{ $judul ?? 'Laporan Transaksi Kayu' }}</h2>

    @if(isset($periode))
        <p>
            <strong>Periode:</strong>
            {{ $periode }}
        </p>
    @endif

    <p>
        <strong>Dicetak pada:</strong>
        {{ now()->translatedFormat('H:i - l - d/m/Y') }}
    </p>

    {{-- ========================= PEMBELIAN ========================= --}}

    <h3 style="margin-top:20px;">
        Laporan Pembelian Kayu
    </h3>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Atas Nama</th>
                <th>Jenis Kayu</th>
                <th>Volume (m³)</th>
                <th>Total Pembelian</th>
                <th>Tanggal</th>
            </tr>
        </thead>

        <tbody>

            @forelse($pembelian as $item)

                <tr>

                    <td>
                        {{ $loop->iteration }}
                    </td>

                    <td>
                        {{ $item->atas_nama }}
                    </td>

                    <td>
                        {{ $item->jenis_kayu ?? '-' }}
                    </td>

                    <td>
                        {{ number_format((float)$item->total_volume, 3, ',', '.') }}
                    </td>

                    <td>
                        Rp{{ number_format((float)$item->total_pembayaran, 0, ',', '.') }}
                    </td>

                    <td>
                        {{ \Carbon\Carbon::parse($item->tanggal_transaksi)->format('d-m-Y') }}
                    </td>

                </tr>

            @empty

                <tr>
                    <td colspan="6" style="text-align:center">
                        Tidak ada data pembelian.
                    </td>
                </tr>

            @endforelse

        </tbody>

        <tfoot>

            <tr>

                <th colspan="3" style="text-align:right">
                    Total Pembelian
                </th>

                <th>
                    {{ number_format($totalVolumePembelian, 3, ',', '.') }}
                </th>

                <th>
                    Rp{{ number_format($totalPembayaranPembelian, 0, ',', '.') }}
                </th>

                <th></th>

            </tr>

        </tfoot>

    </table>

    <br><br>

    {{-- ========================= PENJUALAN ========================= --}}

    <h3>
        Laporan Penjualan Kayu
    </h3>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Atas Nama</th>
                <th>Jenis Kayu</th>
                <th>Volume (m³)</th>
                <th>Total Penjualan</th>
                <th>Tanggal</th>
            </tr>
        </thead>

        <tbody>

            @forelse($penjualan as $item)

                <tr>

                    <td>
                        {{ $loop->iteration }}
                    </td>

                    <td>
                        {{ $item->atas_nama }}
                    </td>

                    <td>
                        {{ $item->jenis_kayu ?? '-' }}
                    </td>

                    <td>
                        {{ number_format((float)$item->total_volume, 3, ',', '.') }}
                    </td>

                    <td>
                        Rp{{ number_format((float)$item->total_pembayaran, 0, ',', '.') }}
                    </td>

                    <td>
                        {{ \Carbon\Carbon::parse($item->tanggal_transaksi)->format('d-m-Y') }}
                    </td>

                </tr>

            @empty

                <tr>
                    <td colspan="6" style="text-align:center">
                        Tidak ada data penjualan.
                    </td>
                </tr>

            @endforelse

        </tbody>

        <tfoot>

            <tr>

                <th colspan="3" style="text-align:right">
                    Total Penjualan
                </th>

                <th>
                    {{ number_format($totalVolumePenjualan, 3, ',', '.') }}
                </th>

                <th>
                    Rp{{ number_format($totalPembayaranPenjualan, 0, ',', '.') }}
                </th>

                <th></th>

            </tr>

        </tfoot>

    </table>

@endsection -->