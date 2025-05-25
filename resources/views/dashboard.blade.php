@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
@php
    $mainGreen = '#33691E';
    $lightGreen = '#A5D6A7';
@endphp
<style>
    .dashboard-card {
        border-radius: 10px;
        box-shadow: 0 2px 8px #e0f2f1;
        overflow: hidden;
        margin-bottom: 32px;
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    .dashboard-card .card-header {
        background: #33691E;
        color: #fff;
        font-weight: 700;
        font-size: 1.15rem;
        letter-spacing: 1px;
        border-bottom: 1px solid #A5D6A7;
    }
    .dashboard-card .card-body {
        padding: 18px 12px;
        background: #fff;
        flex: 1 1 auto;
    }
    .dashboard-table th, .dashboard-table td {
        border: 1px solid #c5e1a5;
        text-align: center;
        padding: 4px 6px;
        font-size: 0.97rem;
        word-break: break-word;
    }
    .dashboard-table th {
        background: #33691E;
        color: #fff;
        font-weight: 600;
    }
    .dashboard-table .row-label {
        background: #f1f8e9;
        font-weight: bold;
        color: #33691E;
    }
    h1 {
        color: #33691E;
        font-weight: 700;
        letter-spacing: 1px;
    }
    /* Responsive: Tablet */
    @media (max-width: 991.98px) {
        .dashboard-card .row {
            flex-direction: column !important;
        }
        .dashboard-card .col-md-8, .dashboard-card .col-md-4 {
            width: 100% !important;
            max-width: 100% !important;
            margin-bottom: 12px;
        }
        .dashboard-card .col-md-4 {
            margin-bottom: 0;
        }
        .dashboard-table th, .dashboard-table td {
            font-size: 0.92rem;
            padding: 4px 3px;
        }
        h1 { font-size: 1.1rem; }
    }
    /* Responsive: Mobile */
    @media (max-width: 575.98px) {
        .dashboard-card {
            margin-bottom: 18px;
        }
        .dashboard-card .card-body {
            padding: 10px 2px;
        }
        .dashboard-table th, .dashboard-table td {
            font-size: 0.86rem;
            padding: 3px 2px;
        }
        .dashboard-card .row {
            flex-direction: column !important;
        }
        .dashboard-card .col-md-8, .dashboard-card .col-md-4 {
            width: 100% !important;
            max-width: 100% !important;
            margin-bottom: 10px;
        }
        .dashboard-card .col-md-4 {
            margin-bottom: 0;
        }
        h1 { font-size: 1rem; }
        .dashboard-table {
            min-width: 220px;
            font-size: 0.85rem;
        }
        .dashboard-table th, .dashboard-table td {
            white-space: nowrap;
        }
        .dashboard-table {
            overflow-x: auto;
            display: block;
        }
    }
    @media print {
        @page {
            size: A4 landscape;
            margin: 1cm;
        }
        html, body {
            width: 100%;
            height: auto;
            zoom: 0.8;
        }
        .d-print-none { display: none !important; }
        .btn, .navbar, .sidebar, .footer {
            display: none !important;
        }
        body, .container-fluid, .dashboard-card {
            background: #fff !important;
            color: #000 !important;
            box-shadow: none !important;
        }
        h1, .card-header {
            color: #000 !important;
        }
        .dashboard-card .row {
            display: flex !important;
            flex-wrap: nowrap !important;
            align-items: flex-start !important;
        }
        .dashboard-card .col-md-8, 
        .dashboard-card .col-md-4 {
            float: none !important;
            display: block !important;
            width: 66.6667% !important;
            max-width: 66.6667% !important;
            box-sizing: border-box;
        }
        .dashboard-card .col-md-4 {
            width: 33.3333% !important;
            max-width: 33.3333% !important;
        }
        .dashboard-card .mb-3 {
            margin-bottom: 0 !important;
        }
        .row {
            display: flex !important;
            flex-wrap: wrap !important;
            gap: 0 !important;
        }
        .col-md-6 {
            flex: 0 0 50% !important;
            max-width: 50% !important;
            box-sizing: border-box;
        }
        .mb-4 {
            margin-bottom: 0.5rem !important;
        }
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="text-center mb-4 flex-grow-1">Statistik Stock</h1>
    <button onclick="window.print()" class="btn btn-outline-success d-print-none ms-3">
        <i class="bi bi-printer"></i> Print Laporan
    </button>
</div>

<div class="d-none d-print-block mb-3 text-end" style="font-size:13px;">
    Dicetak: {{ now()->format('d-m-Y H:i') }}
</div>

<div class="d-flex justify-content-center">
    <div style="width:100%; max-width: 98vw;">
        <div class="row">
            @foreach($woodTypes as $type)
                <div class="col-md-6 mb-4">
                    <div class="dashboard-card card">
                        <div class="card-header text-center">
                            {{ $type }}
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8 mb-3 mb-md-0">
                                    <canvas id="chart-{{ Str::slug($type) }}" style="cursor:pointer; width: 100%; height: 300px;"></canvas>
                                </div>
                                <div class="col-md-4">
                                    <table class="dashboard-table w-100 mb-0">
                                        <thead>
                                            <tr>
                                                <th>Grade</th>
                                                <th class="text-end">Pcs</th>
                                                <th class="text-end">Volume (M³)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($stockData[$type] as $row)
                                                <tr>
                                                    <td class="row-label">{{ $gradeRanges[$row['grade']] ?? $row['grade'] }}</td>
                                                    <td class="text-end">{{ $row['pcs'] ?? 0 }}</td>
                                                    <td class="text-end">{{ number_format($row['volume'], 3) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
<script>
  Chart.register(window.ChartDataLabels);

  const woodTypes = @json($woodTypes);
  const stockData = @json($stockData);

  const mainGreen = '#33691E';
  const lightGreen = '#A5D6A7';
  const barColors = [
    mainGreen, '#558B2F', '#689F38', '#8BC34A', '#AED581', lightGreen, '#C5E1A5'
  ];

  woodTypes.forEach(function(type, idx) {
    const data = stockData[type] || [];
    const ctx = document.getElementById('chart-' + type.toLowerCase().replace(/\s+/g, '-')).getContext('2d');
    new Chart(ctx, {
      type: 'bar',
      data: {
        labels: data.map(item => item.grade),
        datasets: [{
          label: 'Volume M³',
          data: data.map(item => item.volume),
          backgroundColor: data.map((_, i) => barColors[i % barColors.length]),
          borderRadius: 6,
          barPercentage: 0.7
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: { enabled: true },
          datalabels: {
            anchor: 'end',
            align: 'end',
            color: mainGreen,
            font: { weight: 'bold', size: 13 },
            formatter: function(value) {
              return value.toLocaleString(undefined, { minimumFractionDigits: 3, maximumFractionDigits: 3 });
            }
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: { color: mainGreen, font: { weight: 'bold' } },
            grid: { color: '#C5E1A5' }
          },
          x: {
            ticks: { color: mainGreen, font: { weight: 'bold' } },
            grid: { display: false }
          }
        }
      }
    });

    // Event klik pada canvas chart (opsional)
    document.getElementById('chart-' + type.toLowerCase().replace(/\s+/g, '-')).onclick = function() {
      window.location = "{{ url('/stock') }}/" + type;
    };
  });
</script>
@endsection