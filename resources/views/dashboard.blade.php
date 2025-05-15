@extends('layouts.app')

@section('content')
<div class="container-fluid" style="min-height: 100vh; background: #f5f8ee; padding: 40px;">
  <h1 class="text-center mb-4" style="color: #7c5c4a; font-size: 2.2rem; letter-spacing: 1px;">
    Statistik Stock
  </h1>
  
  @foreach($woodTypes as $type)
    <div class="card mb-4">
      <!-- Header Card dengan nama jenis kayu -->
      <div class="card-header text-center" style="background: #7c5c4a; color: #fff; font-weight: 600;">
        {{ $type }}
      </div>
      
      <div class="card-body">
        <div class="row">
          <!-- Kolom Chart: memanfaatkan 8 dari 12 kolom -->
          <div class="col-md-8 mb-3 mb-md-0">
            <canvas id="chart-{{ Str::slug($type) }}" style="cursor:pointer; width: 100%; height: 300px;"></canvas>
          </div>
          <!-- Kolom Tabel: lebar 4 kolom -->
          <div class="col-md-4">
            <table class="table table-striped table-bordered mb-0">
              <thead class="table-light">
                <tr>
                  <th>Grade</th>
                  <th class="text-end">Total Pcs</th>
                  <th class="text-end">Volume (M³)</th>
                </tr>
              </thead>
              <tbody>
                @foreach($stockData[$type] as $row)
                  <tr>
                    <td>{{ $gradeRanges[$row['grade']] ?? $row['grade'] }}</td>
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
  @endforeach
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
<script>
  Chart.register(window.ChartDataLabels);

  const woodTypes = @json($woodTypes);
  const stockData = @json($stockData);

  // Palet warna hijau konsisten dengan stock_detail
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