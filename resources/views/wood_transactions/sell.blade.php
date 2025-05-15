@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Jual Stok</h1>
    <form action="{{ route('wood_transactions.storeSell') }}" method="POST">
        @csrf

        @foreach($woodTypes as $type)
        <h3>{{ $type->name }}</h3>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Grade</th>
                    <th>Diameter</th>
                    <th>Panjang</th>
                    <th>Stok Tersedia</th>
                    <th>Quantity (pcs)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($woodTransactions->where('jenis_kayu_id', $type->id) as $transaction)
                <tr>
                    <td>
                        <input type="hidden" name="id[]" value="{{ $transaction->id }}">
                        {{ $transaction->grade->name }}
                    </td>
                    <td>{{ $transaction->diameter }}</td>
                    <td>{{ $transaction->panjang }}</td>
                    <td>{{ $transaction->quantity }}</td>
                    <td>
                        <input type="number" name="quantity[]" class="form-control" required>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endforeach

        <!-- Input untuk penjualan -->
        <button type="submit" class="btn btn-danger">Kurangi Stok</button>
    </form>
</div>
@endsection