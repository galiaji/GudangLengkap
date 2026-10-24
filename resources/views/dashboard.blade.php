@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
@php
    // Format angka Indonesia: 1.234 dan 1.234,567
    $fPcs = fn ($n) => number_format((int) $n, 0, ',', '.');
    $fVol = fn ($n) => number_format((float) $n, 3, ',', '.');
    $chartTypes = $woodTypes->map(fn ($t) => ['id' => $t->id, 'name' => $t->name])->values();
@endphp
<style>
    :root {
        --dash-green: #33691E;      /* judul & header */
        --dash-bar: #558B2F;        /* warna batang grafik */
        --dash-soft: #f1f8e9;
        --dash-line: #dcedc8;
        --dash-ink: #1f2d16;        /* teks utama */
        --dash-muted: #6b7a63;      /* teks sekunder */
    }
    .dash-title {
        color: var(--dash-green);
        font-weight: 700;
        letter-spacing: .5px;
        margin-bottom: .25rem;
    }
    .dash-subtitle { color: var(--dash-muted); margin-bottom: 1.25rem; }

    /* Ringkasan atas */
    .stat-tile {
        background: #fff;
        border: 1px solid var(--dash-line);
        border-radius: 12px;
        padding: 14px 18px;
        height: 100%;
    }
    .stat-label { color: var(--dash-muted); font-size: .85rem; margin-bottom: 2px; }
    .stat-value { color: var(--dash-ink); font-size: 1.6rem; font-weight: 700; line-height: 1.2; }
    .stat-unit { color: var(--dash-muted); font-size: .9rem; font-weight: 500; }

    /* Kartu per jenis kayu */
    .dash-card {
        background: #fff;
        border: 1px solid var(--dash-line);
        border-radius: 12px;
        overflow: hidden;
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    .dash-card-header {
        background: var(--dash-green);
        color: #fff;
        padding: 12px 16px;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
    }
    .dash-card-header h2 { font-size: 1.15rem; font-weight: 700; margin: 0; }
    .dash-card-header .totals { font-size: .9rem; opacity: .95; }
    .dash-card-header .totals strong { font-size: 1rem; }
    .dash-card-body { padding: 14px 16px 16px; flex: 1 1 auto; }
    .chart-wrap { position: relative; width: 100%; }
    .chart-caption { color: var(--dash-muted); font-size: .8rem; margin: 0 0 6px; }

    /* Tabel */
    .dash-table { width: 100%; border-collapse: collapse; font-size: .92rem; margin-top: 12px; }
    .dash-table th {
        background: var(--dash-soft);
        color: var(--dash-green);
        font-weight: 600;
        padding: 7px 8px;
        border-bottom: 2px solid var(--dash-line);
        white-space: nowrap;
    }
    .dash-table td { padding: 6px 8px; border-bottom: 1px solid var(--dash-line); color: var(--dash-ink); }
    .dash-table .num { text-align: right; font-variant-numeric: tabular-nums; white-space: nowrap; }
    .dash-table .zero { color: #a3ad9d; }
    .dash-table tfoot td { font-weight: 700; background: var(--dash-soft); border-top: 2px solid var(--dash-line); }
    .share-track { display: inline-block; width: 60px; height: 8px; border-radius: 4px; background: var(--dash-line); vertical-align: middle; margin-right: 8px; overflow: hidden; }
    .share-bar { display: block; height: 100%; border-radius: 4px; background: var(--dash-bar); }
    .share-text { display: inline-block; min-width: 3.2em; text-align: right; font-variant-numeric: tabular-nums; }
    .detail-link { color: #fff; border: 1px solid rgba(255,255,255,.6); border-radius: 6px; padding: 2px 10px; font-size: .85rem; text-decoration: none; }
    .detail-link:hover { background: rgba(255,255,255,.15); color: #fff; }

    @media (max-width: 575.98px) {
        .stat-value { font-size: 1.3rem; }
        .dash-table { font-size: .85rem; }
        .dash-table th, .dash-table td { padding: 5px 4px; }
        .col-share { display: none; }
    }
    @media print {
        @page { size: A4 landscape; margin: 1cm; }
        .d-print-none, .btn, .navbar, .sidebar, .footer, .detail-link { display: none !important; }
        .dash-card, .stat-tile { break-inside: avoid; box-shadow: none !important; }
        .col-xl-6 { flex: 0 0 50% !important; max-width: 50% !important; }
    }
</style>

<div class="container-fluid px-2 px-md-3">
    <h1 class="dash-title text-center">Statistik Stok Kayu</h1>
    <p class="dash-subtitle text-center">Posisi stok per {{ now()->translatedFormat('d F Y, H:i') }}</p>

    {{-- Ringkasan semua jenis kayu --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-6 col-xl">
            <div class="stat-tile" style="background: var(--dash-soft);">
                <div class="stat-label">Total semua jenis kayu</div>
                <div class="stat-value">{{ $fVol($grandVolume) }} <span class="stat-unit">m³</span></div>
                <div class="stat-label mt-1 mb-0">{{ $fPcs($grandPcs) }} pcs</div>
            </div>
        </div>
        @foreach ($woodTypes as $type)
            <div class="col-6 col-md-3 col-xl">
                <div class="stat-tile">
                    <div class="stat-label">{{ $type->name }}</div>
                    <div class="stat-value">{{ $fVol($stockData[$type->id]['total_volume']) }} <span class="stat-unit">m³</span></div>
                    <div class="stat-label mt-1 mb-0">{{ $fPcs($stockData[$type->id]['total_pcs']) }} pcs</div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Kartu per jenis kayu --}}
    <div class="row g-4">
        @forelse ($woodTypes as $type)
            @php $data = $stockData[$type->id]; @endphp
            <div class="col-12 col-xl-6">
                <div class="dash-card">
                    <div class="dash-card-header">
                        <h2>{{ $type->name }}</h2>
                        <div class="d-flex align-items-center gap-3 flex-wrap">
                            <span class="totals">
                                <strong>{{ $fPcs($data['total_pcs']) }}</strong> pcs
                                &nbsp;·&nbsp;
                                <strong>{{ $fVol($data['total_volume']) }}</strong> m³
                            </span>
                            <a href="{{ route('stock.detail', $type->id) }}" class="detail-link">Lihat detail →</a>
                        </div>
                    </div>
                    <div class="dash-card-body">
                        <p class="chart-caption">Volume stok per grade (m³)</p>
                        <div class="chart-wrap" style="height: {{ max(180, count($data['grades']) * 38 + 30) }}px;">
                            <canvas id="chart-{{ $type->id }}"
                                    role="img"
                                    aria-label="Grafik volume stok {{ $type->name }} per grade"></canvas>
                        </div>

                        <div class="table-responsive">
                            <table class="dash-table">
                                <thead>
                                    <tr>
                                        <th class="text-start">Grade</th>
                                        <th class="num">Jumlah (pcs)</th>
                                        <th class="num">Volume (m³)</th>
                                        <th class="text-start col-share">Porsi volume</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($data['grades'] as $row)
                                        <tr>
                                            <td>{{ $row['label'] }}</td>
                                            <td class="num {{ $row['pcs'] === 0 ? 'zero' : '' }}">{{ $fPcs($row['pcs']) }}</td>
                                            <td class="num {{ $row['volume'] == 0 ? 'zero' : '' }}">{{ $fVol($row['volume']) }}</td>
                                            <td class="col-share">
                                                <span class="share-track"><span class="share-bar" style="width: {{ $row['persen'] }}%;"></span></span>
                                                <span class="share-text {{ $row['persen'] == 0 ? 'zero' : '' }}">{{ number_format($row['persen'], 1, ',', '.') }}%</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td>Total</td>
                                        <td class="num">{{ $fPcs($data['total_pcs']) }}</td>
                                        <td class="num">{{ $fVol($data['total_volume']) }}</td>
                                        <td class="col-share">100%</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-secondary text-center">Belum ada jenis kayu yang ditampilkan.</div>
            </div>
        @endforelse
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js"></script>
<script>
(function () {
    if (!window.Chart) return;
    Chart.register(window.ChartDataLabels);

    const woodTypes = @json($chartTypes);
    const stockData = @json($stockData);

    const BAR = '#558B2F';     // satu seri = satu warna
    const INK = '#1f2d16';     // teks nilai
    const MUTED = '#6b7a63';   // teks sumbu
    const GRID = '#e3eed8';

    const fmtVol = v => v.toLocaleString('id-ID', { minimumFractionDigits: 3, maximumFractionDigits: 3 });
    const fmtPcs = v => v.toLocaleString('id-ID');

    // Skala sumbu sama untuk semua jenis kayu, supaya panjang batang bisa dibandingkan antar kartu
    const maxVolume = Math.max(0.001, ...woodTypes.flatMap(t => ((stockData[t.id] || {}).grades || []).map(r => r.volume)));
    // Bulatkan ke angka "rapi" (1, 2, 5, 10, 20, ...) supaya ujung sumbu mudah dibaca
    const niceCeil = v => { const p = Math.pow(10, Math.floor(Math.log10(v))); return [1, 2, 2.5, 5, 10].map(m => m * p).find(n => n >= v); };
    const sharedMax = niceCeil(maxVolume * 1.05);

    woodTypes.forEach(function (type) {
        const canvas = document.getElementById('chart-' + type.id);
        const rows = (stockData[type.id] && stockData[type.id].grades) || [];
        if (!canvas || rows.length === 0) return;

        new Chart(canvas, {
            type: 'bar',
            data: {
                labels: rows.map(r => r.label),
                datasets: [{
                    label: 'Volume (m³)',
                    data: rows.map(r => r.volume),
                    backgroundColor: BAR,
                    hoverBackgroundColor: '#33691E',
                    borderRadius: 4,
                    borderSkipped: 'start',
                    barThickness: 18,
                }]
            },
            options: {
                indexAxis: 'y',               // batang horizontal: label grade mudah dibaca
                responsive: true,
                maintainAspectRatio: false,
                layout: { padding: { right: 70 } },   // ruang untuk angka di ujung batang
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: ctx => {
                                const r = rows[ctx.dataIndex];
                                return [`Volume: ${fmtVol(r.volume)} m³`, `Jumlah: ${fmtPcs(r.pcs)} pcs`, `Porsi: ${r.persen.toLocaleString('id-ID')}%`];
                            }
                        }
                    },
                    datalabels: {
                        anchor: 'end',
                        align: 'end',
                        offset: 4,
                        clamp: true,
                        display: ctx => ctx.dataset.data[ctx.dataIndex] > 0,   // grade kosong tidak diberi label
                        color: INK,
                        font: { weight: '600', size: 12 },
                        formatter: v => fmtVol(v)
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        max: sharedMax,
                        title: { display: true, text: 'm³', color: MUTED, align: 'end', font: { size: 11 } },
                        grid: { color: GRID },
                        border: { display: false },
                        ticks: { color: MUTED, maxTicksLimit: 6, callback: v => v.toLocaleString('id-ID', { maximumFractionDigits: 1 }) }
                    },
                    y: {
                        grid: { display: false },
                        ticks: { color: INK, font: { size: 12 } }
                    }
                },
                onClick: () => { window.location = "{{ url('/stock') }}/" + type.id; },
                onHover: (e, el) => { e.native.target.style.cursor = el.length ? 'pointer' : 'default'; }
            }
        });
    });
})();
</script>
@endsection
