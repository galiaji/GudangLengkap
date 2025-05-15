@extends('layouts.app')

@section('content')
<style>
    .custom-table {
        border-collapse: collapse;
        width: 100%;
        margin-bottom: 2rem;
        background: #fff;
        box-shadow: 0 2px 8px #e0e0e0;
        font-size: 0.98rem;
    }
    .custom-table th, .custom-table td {
        border: 1px solid #888;
        padding: 8px 12px;
        text-align: center;
    }
    .custom-table th {
        background: #bdbdbd;
    }
    .custom-table .grade-col {
        background: #bdbdbd;
        font-weight: bold;
    }
    .custom-table .summary-label {
        background: #ededed;
        font-weight: bold;
        text-align: left;
    }
    .custom-table .green-cell {
        background: #39e639 !important;
        font-weight: bold;
    }
    .custom-table .header-main {
        background: #bdbdbd;
        font-size: 1.1rem;
        letter-spacing: 1px;
    }
    .input-cell {
        width: 60px;
        padding: 4px 6px;
        border-radius: 4px;
        border: 1px solid #bbb;
        text-align: right;
    }
    .input-cell:focus {
        outline: 2px solid #39e639;
        border-color: #39e639;
    }
    @media (min-width: 900px) {
        .flex-row { display: flex; gap: 2rem; }
        .flex-col { flex: 1; }
    }
    @media (max-width: 899px) {
        .flex-row { display: block; }
        .flex-col { width: 100%; }
    }
    .info-table td {
        border: 1px solid #888;
        padding: 6px 10px;
        background: #fff;
        font-size: 1rem;
    }
</style>
<div class="container-fluid">
    <div class="flex-row">
        <div class="flex-col">
            <!-- Tabel Rijek 10-15 -->
            <table class="custom-table">
                <thead>
                    <tr>
                        <th colspan="6" class="header-main">Sengon</th>
                    </tr>
                    <tr>
                        <th class="grade-col" rowspan="2">Grade</th>
                        <th rowspan="2" class="grade-col">Diameter</th>
                        <th colspan="4">Panjang</th>
                    </tr>
                    <tr>
                        <th>100</th>
                        <th>130</th>
                        <th>200</th>
                        <th>260</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach([10,11,12,13,14,15] as $d)
                    <tr>
                        @if($loop->first)
                            <td class="grade-col" rowspan="6" style="vertical-align: middle;">Rijek</td>
                        @endif
                        <td class="grade-col">{{ $d }}</td>
                        @foreach([100,130,200,260] as $p)
                        <td>
                            <input type="text" class="input-cell" name="rijek[{{ $d }}][{{ $p }}]" autocomplete="off">
                        </td>
                        @endforeach
                    </tr>
                    @endforeach
                    <tr>
                        <td class="summary-label" colspan="2">Total PCS</td>
                        <td class="green-cell"></td>
                        <td class="green-cell"></td>
                        <td class="green-cell"></td>
                        <td class="green-cell"></td>
                    </tr>
                    <tr>
                        <td class="summary-label" colspan="2">Total Volume</td>
                        <td class="green-cell"></td>
                        <td class="green-cell"></td>
                        <td class="green-cell"></td>
                        <td class="green-cell"></td>
                    </tr>
                    <tr>
                        <td class="summary-label" colspan="2">Total Pembayaran</td>
                        <td class="green-cell"></td>
                        <td class="green-cell"></td>
                        <td class="green-cell"></td>
                        <td class="green-cell"></td>
                    </tr>
                </tbody>
            </table>
            <!-- Tabel Rijek 16-30 -->
            <table class="custom-table">
                <thead>
                    <tr>
                        <th colspan="6" class="header-main">Sengon</th>
                    </tr>
                    <tr>
                        <th class="grade-col" rowspan="2">Grade</th>
                        <th rowspan="2" class="grade-col">Diameter</th>
                        <th colspan="4">Panjang</th>
                    </tr>
                    <tr>
                        <th>100</th>
                        <th>130</th>
                        <th>200</th>
                        <th>260</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach(range(16,30) as $d)
                    <tr>
                        @if($loop->first)
                            <td class="grade-col" rowspan="15" style="vertical-align: middle;">Rijek</td>
                        @endif
                        <td class="grade-col">{{ $d }}</td>
                        @foreach([100,130,200,260] as $p)
                        <td>
                            <input type="text" class="input-cell" name="rijek[{{ $d }}][{{ $p }}]" autocomplete="off">
                        </td>
                        @endforeach
                    </tr>
                    @endforeach
                    <tr>
                        <td class="summary-label" colspan="2">Total PCS</td>
                        <td class="green-cell"></td>
                        <td class="green-cell"></td>
                        <td class="green-cell"></td>
                        <td class="green-cell"></td>
                    </tr>
                    <tr>
                        <td class="summary-label" colspan="2">Total Volume</td>
                        <td class="green-cell"></td>
                        <td class="green-cell"></td>
                        <td class="green-cell"></td>
                        <td class="green-cell"></td>
                    </tr>
                    <tr>
                        <td class="summary-label" colspan="2">Total Pembayaran</td>
                        <td class="green-cell"></td>
                        <td class="green-cell"></td>
                        <td class="green-cell"></td>
                        <td class="green-cell"></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="flex-col">
            <!-- Story 13-15 -->
            <table class="custom-table">
                <thead>
                    <tr>
                        <th colspan="6" class="header-main">Sengon</th>
                    </tr>
                    <tr>
                        <th class="grade-col" rowspan="2">Grade</th>
                        <th rowspan="2" class="grade-col">Diameter</th>
                        <th colspan="4">Panjang</th>
                    </tr>
                    <tr>
                        <th>100</th>
                        <th>130</th>
                        <th>200</th>
                        <th>260</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach([13,14,15] as $d)
                    <tr>
                        @if($loop->first)
                            <td class="grade-col" rowspan="3" style="vertical-align: middle;">Story</td>
                        @endif
                        <td class="grade-col">{{ $d }}</td>
                        @foreach([100,130,200,260] as $p)
                        <td>
                            <input type="text" class="input-cell" name="story[{{ $d }}][{{ $p }}]" autocomplete="off">
                        </td>
                        @endforeach
                    </tr>
                    @endforeach
                    <tr>
                        <td class="summary-label" colspan="2">Total PCS</td>
                        <td class="green-cell"></td>
                        <td class="green-cell"></td>
                        <td class="green-cell"></td>
                        <td class="green-cell"></td>
                    </tr>
                    <tr>
                        <td class="summary-label" colspan="2">Total Volume</td>
                        <td class="green-cell"></td>
                        <td class="green-cell"></td>
                        <td class="green-cell"></td>
                        <td class="green-cell"></td>
                    </tr>
                    <tr>
                        <td class="summary-label" colspan="2">Total Pembayaran</td>
                        <td class="green-cell"></td>
                        <td class="green-cell"></td>
                        <td class="green-cell"></td>
                        <td class="green-cell"></td>
                    </tr>
                </tbody>
            </table>
            <!-- Story 16-18 -->
            <table class="custom-table">
                <thead>
                    <tr>
                        <th colspan="6" class="header-main">Sengon</th>
                    </tr>
                    <tr>
                        <th class="grade-col" rowspan="2">Grade</th>
                        <th rowspan="2" class="grade-col">Diameter</th>
                        <th colspan="4">Panjang</th>
                    </tr>
                    <tr>
                        <th>100</th>
                        <th>130</th>
                        <th>200</th>
                        <th>260</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach([16,17,18] as $d)
                    <tr>
                        @if($loop->first)
                            <td class="grade-col" rowspan="3" style="vertical-align: middle;">Story</td>
                        @endif
                        <td class="grade-col">{{ $d }}</td>
                        @foreach([100,130,200,260] as $p)
                        <td>
                            <input type="text" class="input-cell" name="story[{{ $d }}][{{ $p }}]" autocomplete="off">
                        </td>
                        @endforeach
                    </tr>
                    @endforeach
                    <tr>
                        <td class="summary-label" colspan="2">Total PCS</td>
                        <td class="green-cell"></td>
                        <td class="green-cell"></td>
                        <td class="green-cell"></td>
                        <td class="green-cell"></td>
                    </tr>
                    <tr>
                        <td class="summary-label" colspan="2">Total Volume</td>
                        <td class="green-cell"></td>
                        <td class="green-cell"></td>
                        <td class="green-cell"></td>
                        <td class="green-cell"></td>
                    </tr>
                    <tr>
                        <td class="summary-label" colspan="2">Total Pembayaran</td>
                        <td class="green-cell"></td>
                        <td class="green-cell"></td>
                        <td class="green-cell"></td>
                        <td class="green-cell"></td>
                    </tr>
                </tbody>
            </table>
            <!-- Super 19-25 -->
            <table class="custom-table">
                <thead>
                    <tr>
                        <th colspan="6" class="header-main">Sengon</th>
                    </tr>
                    <tr>
                        <th class="grade-col" rowspan="2">Grade</th>
                        <th rowspan="2" class="grade-col">Diameter</th>
                        <th colspan="4">Panjang</th>
                    </tr>
                    <tr>
                        <th>100</th>
                        <th>130</th>
                        <th>200</th>
                        <th>260</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach(range(19,25) as $d)
                    <tr>
                        @if($loop->first)
                            <td class="grade-col" rowspan="7" style="vertical-align: middle;">Super</td>
                        @endif
                        <td class="grade-col">{{ $d }}</td>
                        @foreach([100,130,200,260] as $p)
                        <td>
                            <input type="text" class="input-cell" name="super[{{ $d }}][{{ $p }}]" autocomplete="off">
                        </td>
                        @endforeach
                    </tr>
                    @endforeach
                    <tr>
                        <td class="summary-label" colspan="2">Total PCS</td>
                        <td class="green-cell"></td>
                        <td class="green-cell"></td>
                        <td class="green-cell"></td>
                        <td class="green-cell"></td>
                    </tr>
                    <tr>
                        <td class="summary-label" colspan="2">Total Volume</td>
                        <td class="green-cell"></td>
                        <td class="green-cell"></td>
                        <td class="green-cell"></td>
                        <td class="green-cell"></td>
                    </tr>
                    <tr>
                        <td class="summary-label" colspan="2">Total Pembayaran</td>
                        <td class="green-cell"></td>
                        <td class="green-cell"></td>
                        <td class="green-cell"></td>
                        <td class="green-cell"></td>
                    </tr>
                </tbody>
            </table>
            <!-- Story 25-30 -->
            <table class="custom-table">
                <thead>
                    <tr>
                        <th colspan="6" class="header-main">Sengon</th>
                    </tr>
                    <tr>
                        <th class="grade-col" rowspan="2">Grade</th>
                        <th rowspan="2" class="grade-col">Diameter</th>
                        <th colspan="4">Panjang</th>
                    </tr>
                    <tr>
                        <th>100</th>
                        <th>130</th>
                        <th>200</th>
                        <th>260</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach(range(25,30) as $d)
                    <tr>
                        @if($loop->first)
                            <td class="grade-col" rowspan="6" style="vertical-align: middle;">Story</td>
                        @endif
                        <td class="grade-col">{{ $d }}</td>
                        @foreach([100,130,200,260] as $p)
                        <td>
                            <input type="text" class="input-cell" name="story[{{ $d }}][{{ $p }}]" autocomplete="off">
                        </td>
                        @endforeach
                    </tr>
                    @endforeach
                    <tr>
                        <td class="summary-label" colspan="2">Total PCS</td>
                        <td class="green-cell"></td>
                        <td class="green-cell"></td>
                        <td class="green-cell"></td>
                        <td class="green-cell"></td>
                    </tr>
                    <tr>
                        <td class="summary-label" colspan="2">Total Volume</td>
                        <td class="green-cell"></td>
                        <td class="green-cell"></td>
                        <td class="green-cell"></td>
                        <td class="green-cell"></td>
                    </tr>
                    <tr>
                        <td class="summary-label" colspan="2">Total Pembayaran</td>
                        <td class="green-cell"></td>
                        <td class="green-cell"></td>
                        <td class="green-cell"></td>
                        <td class="green-cell"></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <table class="info-table" style="margin-top:1rem; min-width:350px;">
        <tr>
            <td style="width:120px;">Atas Nama</td>
            <td style="width:20px;">:</td>
            <td><input type="text" name="atas_nama" style="width:200px"></td>
        </tr>
        <tr>
            <td>Total Volume</td>
            <td>:</td>
            <td id="grand-total-volume"></td>
        </tr>
        <tr>
            <td>Total Pembayaran</td>
            <td>:</td>
            <td id="grand-total-pembayaran"></td>
        </tr>
    </table>
</div>
<script>
// filepath: c:\Users\syafa\Skripsi\Pembukuan stok full\gudang-kayu\resources\views\wood_transactions\buy.blade.php
document.addEventListener('DOMContentLoaded', function() {
    function onlyNumber(val) {
        // Ambil angka saja, jika ada huruf, return 0
        let n = parseInt(val);
        return isNaN(n) ? 0 : n;
    }
    function getHargaPerM3() {
        // Contoh harga per m3, bisa diubah sesuai kebutuhan
        return 1000000;
    }
    function updateGrandTotal() {
        let totalVolume = 0;
        let totalPembayaran = 0;
        document.querySelectorAll('.custom-table').forEach(function(table) {
            const summaryRows = table.querySelectorAll('tbody tr');
            const volCells = summaryRows[summaryRows.length-3].querySelectorAll('.green-cell');
            volCells.forEach(cell => {
                let v = parseFloat(cell.textContent.replace(',','.'));
                if(!isNaN(v)) totalVolume += v;
            });
        });
        totalPembayaran = totalVolume * getHargaPerM3();
        document.getElementById('grand-total-volume').textContent = totalVolume.toFixed(3);
        document.getElementById('grand-total-pembayaran').textContent = totalPembayaran.toLocaleString();
    }
    document.querySelectorAll('.custom-table').forEach(function(table) {
        table.querySelectorAll('input.input-cell').forEach(function(input) {
            input.addEventListener('input', function() {
                updateTable(table);
                updateGrandTotal();
            });
        });
        updateTable(table);
    });
    updateGrandTotal();

    function updateTable(table) {
        // Ambil semua baris data (exclude summary rows)
        const rows = Array.from(table.querySelectorAll('tbody tr')).filter(tr =>
            tr.querySelector('input.input-cell')
        );
        const colCount = 4;
        let totalPCS = [0,0,0,0];
        let totalVolume = [0,0,0,0];

        rows.forEach(row => {
            const tds = row.querySelectorAll('td');
            for(let i=0; i<colCount; i++) {
                const input = tds[i+2]?.querySelector('input');
                if(input) {
                    const pcs = onlyNumber(input.value);
                    totalPCS[i] += pcs;
                    const diameter = parseFloat(tds[1].textContent);
                    const panjang = parseFloat(table.querySelectorAll('thead tr')[2].children[i+2].textContent);
                    const volumePerBatang = Math.PI * Math.pow(diameter/2,2) * panjang / 1000000;
                    totalVolume[i] += pcs * volumePerBatang;
                }
            }
        });

        // Update summary row
        const summaryRows = table.querySelectorAll('tbody tr');
        // Total PCS
        summaryRows[summaryRows.length-4].querySelectorAll('.green-cell').forEach((td,i) => {
            td.textContent = totalPCS[i] || '';
        });
        // Total Volume
        summaryRows[summaryRows.length-3].querySelectorAll('.green-cell').forEach((td,i) => {
            td.textContent = totalVolume[i] ? totalVolume[i].toFixed(3) : '';
        });
        // Total Pembayaran
        summaryRows[summaryRows.length-2].querySelectorAll('.green-cell').forEach((td,i) => {
            td.textContent = totalVolume[i] ? (totalVolume[i]*getHargaPerM3()).toLocaleString() : '';
        });
    }
});
</script>
@endsection