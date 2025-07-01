<!-- filepath: resources/views/exports/stock_pdf.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <style>
        @page {
            margin: 8mm 8mm 2mm 8mm; /* atas kanan bawah kiri, atas & bawah minimal */
        }
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .judul { font-size: 17px; font-weight: bold; text-align: center; margin-bottom: 10px; }
        .grade-title { font-size: 14px; font-weight: bold; margin-bottom: 2px; text-align: center; }
        .sub-table {
            border-collapse: collapse;
            width: 100%;
            margin-bottom: 0;
            table-layout: fixed;
        }
        .sub-table th, .sub-table td {
            border: 1px solid #888;
            padding: 1px 2px;
            text-align: center;
            font-size: 13px;
            word-break: break-word;
        }
        .sub-table th {
            background: #eaeaea;
            font-weight: bold;
            font-size: 14px;
        }
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
            $grades = $stocks->where('wood_type', $woodType)->pluck('grade')->unique()->values();
            $gradeCount = count($grades);
        @endphp
        @for($i = 0; $i < $gradeCount; $i += 3)
            <table style="width:100%; table-layout:fixed; margin-bottom: 6px;">
                <tr>
                    @for($j = $i; $j < $i + 3 && $j < $gradeCount; $j++)
                        <td style="vertical-align:top; width:33%;">
                            <div class="grade-title">Grade: {{ $grades[$j] }}</div>
                            @php
                                $panjangs = $stocks->where('wood_type', $woodType)
                                    ->where('grade', $grades[$j])
                                    ->pluck('panjang')->unique()->sort()->values();
                                $diameters = $stocks->where('wood_type', $woodType)
                                    ->where('grade', $grades[$j])
                                    ->pluck('diameter')->unique()->sort()->values();
                            @endphp
                            <table class="sub-table" style="margin-bottom:8px;">
                                <tr>
                                    <th rowspan="2" style="vertical-align: middle;">Diameter</th>
                                    <th colspan="{{ count($panjangs) }}">Panjang</th>
                                </tr>
                                <tr>
                                    @foreach($panjangs as $panjang)
                                        <th>{{ $panjang }}</th>
                                    @endforeach
                                </tr>
                                @foreach($diameters as $diameter)
                                    <tr>
                                        <td>{{ $diameter }}</td>
                                        @foreach($panjangs as $panjang)
                                            @php
                                                $pcs = $stocks->where('wood_type', $woodType)
                                                    ->where('grade', $grades[$j])
                                                    ->where('diameter', $diameter)
                                                    ->where('panjang', $panjang)
                                                    ->sum('quantity');
                                            @endphp
                                            <td>{{ $pcs }}</td>
                                        @endforeach
                                    </tr>
                                @endforeach
                                <tr class="total-row">
                                    <td>Total</td>
                                    @foreach($panjangs as $panjang)
                                        <td>
                                            {{ $stocks->where('wood_type', $woodType)
                                                ->where('grade', $grades[$j])
                                                ->where('panjang', $panjang)
                                                ->sum('quantity') }}
                                        </td>
                                    @endforeach
                                </tr>
                                <tr class="total-row">
                                    <td>Total Volume</td>
                                    @foreach($panjangs as $panjang)
                                        <td>
                                            {{ number_format(
                                                $stocks->where('wood_type', $woodType)
                                                    ->where('grade', $grades[$j])
                                                    ->where('panjang', $panjang)
                                                    ->reduce(function($carry, $item) {
                                                        return $carry + (($item->quantity ?? 0) * ($item->volume ?? 0));
                                                    }, 0)
                                                , 3) }}
                                        </td>
                                    @endforeach
                                </tr>
                            </table>
                        </td>
                    @endfor
                    @for($k = $j; $k < $i + 3; $k++)
                        <td></td>
                    @endfor
                </tr>
            </table>
        @endfor
    @endforeach
</body>
</html>