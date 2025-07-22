<!-- filepath: resources/views/exports/stock_pdf.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <style>
        @page { margin: 15mm 10mm 8mm 20mm; }
        body { font-family: "Times New Roman", Times, serif; font-size: 10.5pt; }
        .judul { font-size: 16pt; font-weight: bold; text-align: center; margin-bottom: 14px; }
        .jenis-kayu { font-size: 12pt; font-weight: bold; margin: 10px 0 6px 0; }
        .grade-table {
            width: 100%;
            table-layout: fixed;
            margin-bottom: 20px;
            border-spacing: 18px 0;
        }
        .grade-td {
            padding: 0 12px 0 0;
            vertical-align: top;
            width: 50%;
        }
        .grade-title {
            font-size: 11.5pt;
            font-weight: bold;
            text-align: center;
            margin-bottom: 4px;
            border-bottom: 1px solid #bbb;
            padding-bottom: 2px;
            letter-spacing: 0.5px;
        }
        .sub-table {
            border-collapse: collapse;
            width: 100%;
            margin-bottom: 0;
            table-layout: auto;
        }
        .sub-table th, .sub-table td {
            border: 1px solid #888;
            padding: 2px 5px;
            text-align: center;
            font-size: 9.5pt;
            word-break: keep-all;
            white-space: nowrap;
        }
        .sub-table th {
            background: #f3f3f3;
            font-weight: bold;
            font-size: 10pt;
        }
        .total-row { font-weight: bold; background: #f5f5f5; }
        .footer-print {
            position: fixed;
            right: 0;
            bottom: 0;
            font-size: 10px;
            text-align: right;
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="judul">Laporan Stock LOG Kayu</div>
    <div class="footer-print">
        Dicetak pada: {{ \Carbon\Carbon::now()->format('d-m-Y H:i') }}
    </div>
    @foreach($woodTypes as $woodType)
        <div class="jenis-kayu">Jenis Kayu : {{ $woodType }}</div>
        @php
            $grades = $stocks->where('wood_type', $woodType)->pluck('grade')->unique()->values();

            // Grade selain "Rijek" di atas (urut alfabet), "Rijek 1" di kiri bawah, "Rijek 2" di kanan bawah
            $grades = $grades->sort(function($a, $b) {
                $isRijekA = stripos($a, 'Rijek') !== false;
                $isRijekB = stripos($b, 'Rijek') !== false;

                if ($isRijekA && !$isRijekB) return 1;   // "Rijek" selalu di bawah
                if (!$isRijekA && $isRijekB) return -1;  // Non-"Rijek" di atas

                // Jika sama-sama "Rijek", urutkan "Rijek 1" dulu, lalu "Rijek 2"
                if ($isRijekA && $isRijekB) {
                    if ($a === 'Rijek 1') return -1;
                    if ($b === 'Rijek 1') return 1;
                    if ($a === 'Rijek 2') return -1;
                    if ($b === 'Rijek 2') return 1;
                    return strcmp($a, $b);
                }

                // Grade lain urut alfabetis
                return strcmp($a, $b);
            })->values();
            $gradeCount = count($grades);
        @endphp
        @for($i = 0; $i < $gradeCount; $i += 2) <!-- Ubah jadi 2 kolom per baris -->
            <table class="grade-table">
                <tr>
                    @for($j = $i; $j < $i + 2 && $j < $gradeCount; $j++)
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
                            <table class="sub-table">
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
                                            <td>{{ $pcs ? number_format($pcs, 0, ',', '.') : '-' }}</td>
                                        @endforeach
                                    </tr>
                                @endforeach
                                <tr class="total-row">
                                    <td>Total</td>
                                    @foreach($panjangs as $panjang)
                                        <td>
                                            {{ number_format(
                                                $stocks->where('wood_type', $woodType)
                                                    ->where('grade', $grades[$j])
                                                    ->where('panjang', $panjang)
                                                    ->sum('quantity') ?: 0, 0, ',', '.'
                                            ) }}
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
                                                , 3, ',', '.') ?: '-' }}
                                        </td>
                                    @endforeach
                                </tr>
                            </table>
                        </td>
                    @endfor
                    @for($k = $j; $k < $i + 2; $k++)
                        <td class="grade-td"></td>
                    @endfor
                </tr>
            </table>
        @endfor
    @endforeach
</body>
</html>