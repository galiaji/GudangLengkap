<!-- filepath: resources/views/exports/stock_pdf.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <style>
        @page {
            margin: 15mm 10mm 8mm 20mm;
        }
        body { font-family: "Times New Roman", Times, serif; font-size: 12pt; }
        .judul {
            font-size: 16pt;
            font-weight: bold;
            text-align: center;
            margin-bottom: 14px;
        }
        .jenis-kayu {
            font-size: 13pt;
            font-weight: bold;
            margin: 8px 0 4px 0;
        }
        .grade-title { font-size: 15pt; font-weight: bold; margin-bottom: 2px; text-align: center; }
        .sub-table {
            border-collapse: collapse;
            margin-bottom: 0;
            table-layout: auto;
        }
        .sub-table th, .sub-table td {
            border: 1px solid #888;
            padding: 3px 8px;
            text-align: center;
            font-size: 12pt;
            word-break: keep-all;
            white-space: nowrap;
        }
        .sub-table th {
            background: #eaeaea;
            font-weight: bold;
            font-size: 13pt;
            white-space: nowrap;
        }
        .total-row { font-weight: bold; background: #f5f5f5; }
        .grade-td {
            padding-right: 12px;
            vertical-align: top;
            width: 33%;
        }
    </style>
</head>
<body>
    <div class="judul">Laporan Stock LOG Kayu</div>
    <div style="position: fixed; right: 0; bottom: 0; font-size: 11px; text-align: right; width: 100%;">
        Dicetak pada: {{ \Carbon\Carbon::now()->format('d-m-Y H:i') }}
    </div>
    @foreach($woodTypes as $woodType)
        <div class="jenis-kayu"> Jenis Kayu : {{ $woodType }}</div>
        @php
            $grades = $stocks->where('wood_type', $woodType)->pluck('grade')->unique()->values();
            $gradeCount = count($grades);
        @endphp
        @for($i = 0; $i < $gradeCount; $i += 3)
            <table style="width:100%; table-layout:fixed; margin-bottom: 6px;">
                <tr>
                    @for($j = $i; $j < $i + 3 && $j < $gradeCount; $j++)
                        <td class="grade-td">
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