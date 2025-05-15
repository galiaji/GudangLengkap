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
        {{ $type->name }}
      </div>
      
      <div class="card-body">
        <div class="row">
          <!-- Kolom Chart: memanfaatkan 8 dari 12 kolom -->
          <div class="col-md-8 mb-3 mb-md-0">
            <canvas id="chart-{{ $type->id }}" style="cursor:pointer; width: 100%; height: 300px;"></canvas>
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
                @foreach($stockData[$type->name] as $row)
                  <tr>
                    <td>{{ $row['grade'] }}</td>
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
  // Daftarkan plugin datalabels ke Chart.js
  Chart.register(window.ChartDataLabels);

  const woodTypes = @json($woodTypes);
  const stockData = @json($stockData);

  woodTypes.forEach(function(type) {
    const data = stockData[type.name] || [];
    const ctx = document.getElementById('chart-' + type.id).getContext('2d');
    new Chart(ctx, {
      type: 'bar',
      data: {
        labels: data.map(item => item.grade),
        datasets: [{
          label: 'Volume M³',
          data: data.map(item => item.volume),
          backgroundColor: '#222',
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
            color: '#7c5c4a',
            font: { weight: 'bold', size: 13 },
            formatter: function(value) {
              return value.toLocaleString(undefined, { minimumFractionDigits: 3, maximumFractionDigits: 3 });
            }
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: { color: '#7c5c4a', font: { weight: 'bold' } },
            grid: { color: '#e0e0d1' }
          },
          x: {
            ticks: { color: '#7c5c4a', font: { weight: 'bold' } },
            grid: { display: false }
          }
        }
      }
    });
    
    // Event klik pada canvas chart (opsional)
    document.getElementById('chart-' + type.id).onclick = function() {
      window.location = "{{ url('/stock') }}/" + type.id;
    };
  });
</script>
@endsection