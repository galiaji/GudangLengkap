@extends('layouts.app')

@section('content')
<style>
    .stock-statistik-card {
        border-radius: 10px;
        box-shadow: 0 2px 8px #e0f2f1;
        overflow: hidden;
        margin-bottom: 32px;
        background: #fff;
        max-width: 1100px;
        margin-left: auto;
        margin-right: auto;
    }
    .stock-statistik-card .card-header {
        background: #33691E;
        color: #fff;
        font-weight: 700;
        font-size: 1.3rem;
        letter-spacing: 1px;
        border-bottom: 1px solid #A5D6A7;
        text-align: center;
        padding: 22px 0 22px 0;
    }
    .stock-statistik-card .card-body {
        padding: 24px 18px;
        background: #fff;
    }
    .stock-statistik-title {
        color: #33691E;
        font-size: 2.2rem;
        letter-spacing: 1px;
        margin-bottom: 2rem;
        text-align: center;
        font-weight: 700;
    }
    .card-header.bg-success {
        background: #388e3c !important;
        font-size: 1.1rem;
        letter-spacing: 0.5px;
    }
    .table th, .table td {
        text-align: center;
        vertical-align: middle;
        font-size: 14px;
        padding: 6px 4px;
        white-space: nowrap;
    }
    .table thead th {
        background: #c8e6c9 !important;
        color: #33691E;
        font-weight: 700;
    }
    .row.g-3 {
        --bs-gutter-x: 1rem;
        --bs-gutter-y: 1.5rem;
    }
    @media (max-width: 1200px) {
        .stock-statistik-title { font-size: 1.5rem; }
        .stock-statistik-card { max-width: 100%; }
    }
    @media (max-width: 768px) {
        .stock-statistik-title { font-size: 1.1rem; }
        .stock-statistik-card .card-body { padding: 10px 2px; }
        .table th, .table td { font-size: 12px; padding: 4px 2px; }
    }
    @media print {
        .d-print-none { display: none !important; }
        .d-print-block { display: block !important; }
        .btn, .navbar, .sidebar, .footer {
            display: none !important;
        }
        .container-fluid {
            position: absolute;
            left: 0;
            top: 0;
            width: 100vw;
            background: #fff !important;
        }
    }
    .d-print-block { display: none; }
</style>
<div class="container-fluid py-4" id="laporan-stock">
    <div class="d-print-none">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
            </div>
        @endif
        <h2 class="stock-statistik-title mb-4">Statistik Stok {{ $woodType->name }}</h2>
        <div class="mb-3 text-end">
            <a href="{{ route('export.stok.pdf', ['woodType' => $woodType->id]) }}" class="btn btn-danger me-2">
                <i class="bi bi-file-earmark-pdf"></i> Export PDF
            </a>
        </div>
    </div>
    <div class="d-none d-print-block mb-4">
        <h2 class="text-center">Laporan Stok {{ $woodType->name }}</h2>
        <div class="text-end" style="font-size:13px;">Dicetak: {{ now()->format('d-m-Y H:i') }}</div>
        <hr>
    </div>
    <div class="row g-4 justify-content-center">
        @php
            $gradesArr = is_array($grades) ? $grades : $grades->toArray();
            $rijek = [];
            $mainGrades = [];
            foreach($gradesArr as $g) {
                if(stripos($g, 'Rijek') !== false) {
                    $rijek[] = $g;
                } else {
                    $mainGrades[] = $g;
                }
            }
            $orderedGrades = array_merge($mainGrades, $rijek);
        @endphp

        @foreach($orderedGrades as $grade)
        <div class="col-lg-4 col-md-6 col-12 mb-3">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white text-center fw-bold">
                    Grade: {{ $gradeRanges[$grade] ?? $grade }}
                </div>
                <div class="card-body p-3">
                    @php
                        $gradeStocks = $stocks->where('grade', $grade);
                        $uniquePanjang = $gradeStocks->pluck('panjang')->unique()->sort();
                        $minDiameter = $gradeStocks->min('diameter');
                        $maxDiameter = $gradeStocks->max('diameter');
                    @endphp

                    <div class="table-responsive">
                        <table class="table table-bordered table-sm mb-0">
                            <thead class="table-success text-center align-middle">
                                <tr>
                                    <th rowspan="2">Diameter</th>
                                    @foreach($uniquePanjang as $p)
                                        <th colspan="2">Panjang {{ $p }}</th>
                                    @endforeach
                                    <th colspan="2">Total</th>
                                </tr>
                                <tr>
                                    @foreach($uniquePanjang as $p)
                                        <th>Pcs</th>
                                        <th>Vol</th>
                                    @endforeach
                                    <th>Pcs</th>
                                    <th>Vol</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if($minDiameter !== null && $maxDiameter !== null)
                                    @for($d = $minDiameter; $d <= $maxDiameter; $d++)
                                        @php
                                            $rowTotalPcs = 0;
                                            $rowTotalVolume = 0;
                                        @endphp
                                        <tr>
                                            <td>{{ $d }}</td>
                                            @foreach($uniquePanjang as $p)
                                                @php
                                                    $items = $gradeStocks->where('diameter', $d)->where('panjang', $p);
                                                    $pcs = $items->sum('quantity');
                                                    $volume = $items->sum('total_volume');
                                                    $rowTotalPcs += $pcs;
                                                    $rowTotalVolume += $volume;
                                                @endphp
                                                <td>{{ $pcs ?: 0 }}</td>
                                                <td>{{ $pcs ? number_format($volume, 3) : '0.000' }}</td>
                                            @endforeach
                                            <td class="fw-bold">{{ $rowTotalPcs }}</td>
                                            <td class="fw-bold">{{ number_format($rowTotalVolume, 3) }}</td>
                                        </tr>
                                    @endfor
                                @endif

                                @php
                                    $gradeTotalPcs = $gradeStocks->sum('quantity');
                                    $gradeTotalVolume = $gradeStocks->sum('total_volume');
                                @endphp
                                <tr class="table-success fw-bold">
                                    <td>Total</td>
                                    @foreach($uniquePanjang as $p)
                                        @php
                                            $pcs = $gradeStocks->where('panjang', $p)->sum('quantity');
                                            $volume = $gradeStocks->where('panjang', $p)->sum('total_volume');
                                        @endphp
                                        <td>{{ $pcs ?: 0 }}</td>
                                        <td>{{ $pcs ? number_format($volume, 3) : '0.000' }}</td>
                                    @endforeach
                                    <td>{{ $gradeTotalPcs }}</td>
                                    <td>{{ number_format($gradeTotalVolume, 3) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
