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
    }
    .dashboard-table th, .dashboard-table td {
        border: 1px solid #c5e1a5;
        text-align: center;
        padding: 4px 6px;
        font-size: 0.97rem;
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
    @media (max-width: 768px) {
        .dashboard-table th, .dashboard-table td { font-size: 0.89rem; }
        h1 { font-size: 1.2rem; }
    }
</style>

<div class="d-flex justify-content-center">
    <div style="width:100%; max-width: 98vw;">
        <h1 class="text-center mb-4">Statistik Stock</h1>
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