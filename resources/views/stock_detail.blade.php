@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <h1 class="text-center mb-4" style="color:#33691E; font-size:2.2rem; letter-spacing:1px;">
        Statistik Stok {{ $woodType->name }}
    </h1>

    <div class="row g-4">
        @foreach($grades as $grade)
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white text-center fw-bold">
                    Grade: {{ $grade->name }}
                </div>
                <div class="card-body p-3">
                    @php
                        $gradeStocks = $stocks->where('grade_id', $grade->id);
                        $uniquePanjang = $gradeStocks->pluck('panjang')->unique()->sort();
                    @endphp

                    <div class="table-responsive">
                        <table class="table table-bordered table-sm" style="font-size:14px;">
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
                                @for($d = $grade->min_diameter; $d <= $grade->max_diameter; $d++)
                                    @php
                                        $rowTotalPcs = 0;
                                        $rowTotalVolume = 0;
                                    @endphp
                                    <tr class="text-center">
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

                                @php
                                    $gradeTotalPcs = $gradeStocks->sum('quantity');
                                    $gradeTotalVolume = $gradeStocks->sum('total_volume');
                                @endphp
                                <tr class="table-success fw-bold text-center">
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
                    </div> <!-- table-responsive -->
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
