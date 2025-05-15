@extends('layouts.app')

@section('content')
<div style="background:#222; color:#fff; min-height:100vh;">
    <div style="padding:30px;">
        <h1 style="text-align:center; color:#7c5c4a; margin-bottom:30px;">Statistik Stock {{ $woodType->name }}</h1>
        <div style="display:flex; flex-wrap:wrap; gap:30px; justify-content:center;">
            @foreach($grades as $grade)
            <div style="background:#fff; color:#222; border-radius:10px; box-shadow:0 2px 8px #0001; padding:18px 16px; min-width:350px;">
                <div style="font-weight:bold; color:#388e3c; margin-bottom:10px;">
                    Grade: {{ $grade->name }}
                </div>
                <table style="width:100%; border-collapse:collapse; font-size:15px; background:#f9f9f9; border-radius:8px; overflow:hidden;">
                    <thead>
                        <tr style="background:#e0e0d1;">
                            <th style="padding:6px 8px;">Diameter</th>
                            <th style="padding:6px 8px;">Panjang</th>
                            <th style="padding:6px 8px;">Pcs</th>
                            <th style="padding:6px 8px;">Volume</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $gradeStocks = $stocks->where('grade_id', $grade->id)->groupBy('diameter');
                            $total = 0;
                        @endphp
                        @for($d = $grade->min_diameter; $d <= $grade->max_diameter; $d++)
                            @php
                                $items = $gradeStocks->get($d, collect());
                                $hasData = $items->count() > 0;
                            @endphp
                            @if($hasData)
                                @foreach($items as $item)
                                <tr>
                                    <td style="padding:6px 8px;">{{ $d }}</td>
                                    <td style="padding:6px 8px;">{{ $item->panjang }}</td>
                                    <td style="padding:6px 8px;">{{ $item->quantity }}</td>
                                    <td style="padding:6px 8px;">{{ number_format($item->total_volume, 3) }}</td>
                                    @php $total += $item->total_volume; @endphp
                                </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td style="padding:6px 8px;">{{ $d }}</td>
                                    <td style="padding:6px 8px;">-</td>
                                    <td style="padding:6px 8px;">0</td>
                                    <td style="padding:6px 8px;">0.000</td>
                                </tr>
                            @endif
                        @endfor
                        <tr style="background:#c8ffc8;">
                            <td colspan="3" style="text-align:right; font-weight:bold;">Total</td>
                            <td style="font-weight:bold;">{{ number_format($total, 3) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection