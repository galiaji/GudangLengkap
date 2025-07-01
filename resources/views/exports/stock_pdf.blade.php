<!-- filepath: resources/views/exports/stock_pdf.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; }
        .judul { font-size: 15px; font-weight: bold; text-align: center; margin-bottom: 12px; }
        .main-table { border-collapse: collapse; width: 100%; }
        .main-table th, .main-table td { border: 1px solid #000; padding: 2px 4px; vertical-align: top; }
        .grade-title { font-size: 12px; font-weight: bold; margin-bottom: 2px; text-align: center; }
        .sub-table { border-collapse: collapse; width: 100%; margin-bottom: 0; }
        .sub-table th, .sub-table td { border: 1px solid #888; padding: 1px 3px; text-align: center; font-size: 10px; }
        .sub-table th { background: #eaeaea; font-weight: bold; }
        .total-row { font-weight: bold; background: #f5f5f5; }
    </style>
</head>
<body>
    <div class="judul">Laporan Stock LOG Kayu</div>
    <div style="text-align:right; font-size:11px; margin-bottom:8px;">
        Dicetak: {{ \Carbon\Carbon::now()->format('d-m-Y H:i') }}
    </div>
    @foreach($woodTypes as $woodType)
        <div style="font-size:13px;font-weight:bold;margin:8px 0 4px 0;">{{ $woodType }}</div>
        @php
            $grades = $stocks->where('wood_type', $woodType)->pluck('grade')->unique();
        @endphp
        <table class="main-table">
            <tr>
                @foreach($grades as $grade)
                    <th>
                        <div class="grade-title">Grade: {{ $grade }}</div>
                    </th>
                @endforeach
            </tr>
            <tr>
                @foreach($grades as $grade)
                    <td>
                        <table class="sub-table">
                            <tr>
                                <th>Panjang</th>
                                <th>Diameter</th>
                                <th>Pcs</th>
                                <th>Volume (M³)</th>
                            </tr>
                            @php
                                $panjangs = $stocks->where('wood_type', $woodType)
                                    ->where('grade', $grade)
                                    ->pluck('panjang')->unique()->sort();
                            @endphp
                            @foreach($panjangs as $panjang)
                                @php
                                    $rows = $stocks->where('wood_type', $woodType)
                                        ->where('grade', $grade)
                                        ->where('panjang', $panjang);
                                    $totalPcsPanjang = $rows->sum('quantity');
                                    $totalVolumePanjang = $rows->reduce(function($carry, $item) {
                                        return $carry + (($item->quantity ?? 0) * ($item->volume ?? 0));
                                    }, 0);
                                @endphp
                                @foreach($rows as $row)
                                    <tr>
                                        <td>{{ $row->panjang ?? '-' }}</td>
                                        <td>{{ $row->diameter ?? '-' }}</td>
                                        <td>{{ $row->quantity ?? 0 }}</td>
                                        <td>{{ number_format(($row->quantity ?? 0) * ($row->volume ?? 0), 3) }}</td>
                                    </tr>
                                @endforeach
                                <tr class="total-row">
                                    <td colspan="2">Total Panjang {{ $panjang }}</td>
                                    <td>{{ $totalPcsPanjang }}</td>
                                    <td>{{ number_format($totalVolumePanjang, 3) }}</td>
                                </tr>
                            @endforeach
                            @php
                                $totalPcs = $stocks->where('wood_type', $woodType)
                                    ->where('grade', $grade)
                                    ->sum('quantity');
                                $totalVolume = $stocks->where('wood_type', $woodType)
                                    ->where('grade', $grade)
                                    ->reduce(function($carry, $item) {
                                        return $carry + (($item->quantity ?? 0) * ($item->volume ?? 0));
                                    }, 0);
                            @endphp
                            <tr class="total-row">
                                <td colspan="2">Total Grade</td>
                                <td>{{ $totalPcs }}</td>
                                <td>{{ number_format($totalVolume, 3) }}</td>
                            </tr>
                        </table>
                    </td>
                @endforeach
            </tr>
        </table>
    @endforeach
</body>
</html>