@extends('layouts.app')

@section('content')
<div style="background:#222; color:#fff; min-height:100vh;">
    <div style="display:flex;">
        <!-- Sidebar -->
        <div style="width:220px; background:#2e7d32; min-height:100vh; padding:20px;">
            <h2 style="color:#fff; margin-bottom:30px;">UD. TPKS</h2>
            <a href="#" style="display:block; margin:12px 0; color:#fff; font-weight:500;">Laporan Stock</a>
            <a href="#" style="display:block; margin:12px 0; color:#fff; font-weight:500;">Seting Harga</a>
            <a href="#" style="display:block; margin:12px 0; color:#fff; font-weight:500;">Transaksi</a>
            <button style="margin-top:40px; background:#388e3c; color:#fff; border:none; padding:12px 24px; border-radius:20px; font-weight:600;">Tambah Akun</button>
        </div>
        <!-- Main Content -->
        <div style="flex:1; background:#f5f8ee; padding:40px;">
            <h1 style="text-align:center; color:#7c5c4a; margin-bottom:40px; font-size:2.2rem; letter-spacing:1px;">Statistik Stock</h1>
            <div style="display:flex; flex-wrap:wrap; justify-content:center; gap:32px;">
                @foreach($woodTypes as $type)
                <div style="background:#fff; border-radius:14px; box-shadow:0 2px 12px #0002; padding:28px 22px 18px 22px; width:370px; margin-bottom:20px;">
                    <div style="font-weight:700; color:#7c5c4a; margin-bottom:18px; text-align:center; font-size:1.2rem; cursor:pointer;"
                         onclick="window.location='{{ route('stock.detail', $type->id) }}'">
                        {{ $type->name }}
                    </div>
                    <canvas id="chart-{{ $type->id }}" width="320" height="180" style="margin-bottom:18px; cursor:pointer;"></canvas>
                    <table style="width:100%; border-collapse:collapse; font-size:15px; color:#333; background:#f9f9f9; border-radius:8px; overflow:hidden;">
                        <thead>
                            <tr style="background:#e0e0d1;">
                                <th style="padding:7px 10px; text-align:left;">Grade</th>
                                <th style="padding:7px 10px; text-align:right;">Volume (M³)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($stockData[$type->name] as $row)
                            <tr style="border-bottom:1px solid #ececec;">
                                <td style="padding:7px 10px;">{{ $row['grade'] }}</td>
                                <td style="padding:7px 10px; text-align:right;">{{ number_format($row['volume'], 3) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
<script>
    // Daftarkan plugin datalabels ke Chart.js
    Chart.register(window.ChartDataLabels);

    const woodTypes = @json($woodTypes);
    const stockData = @json($stockData);

    woodTypes.forEach(function(type) {
        const data = stockData[type.name] || [];
        const ctx = document.getElementById('chart-' + type.id).getContext('2d');
        const chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.map(item => item.grade),
                datasets: [{
                    label: 'Volume M³',
                    data: data.map(item => item.volume),
                    backgroundColor: '#222',
                    borderRadius: 6,
                    barPercentage: 0.7,
                }]
            },
            options: {
                plugins: {
                    legend: { display: false },
                    tooltip: { enabled: true },
                    datalabels: {
                        anchor: 'end',
                        align: 'end',
                        color: '#7c5c4a',
                        font: { weight: 'bold', size: 13 },
                        formatter: function(value) {
                            return value.toLocaleString(undefined, {minimumFractionDigits: 3, maximumFractionDigits: 3});
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { color: '#7c5c4a', font: { weight: 'bold' } },
                        grid: { color: '#e0e0d1' }
                    },
                    x: {
                        ticks: { color: '#7c5c4a', font: { weight: 'bold' } },
                        grid: { display: false }
                    }
                }
            }
        });
        // Tambahkan event klik pada canvas chart
        document.getElementById('chart-' + type.id).onclick = function() {
            window.location = "{{ url('/stock') }}/" + type.id;
        };
    });
</script>
@endsection