@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto py-12">
    <h2 class="text-2xl font-semibold mb-4">Terima kasih!</h2>

    @if(session('success'))
        <div class="bg-green-100 border border-green-300 text-green-700 px-4 py-2 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <p>Pesanan #{{ $id }} telah dicatat dengan metode <strong>Bayar di Lokasi</strong>. 
       Admin akan segera memproses pesanan Anda.</p>

    <a href="{{ url('/') }}" class="inline-block mt-6 px-4 py-2 bg-pink-600 text-white rounded">
        Kembali ke Beranda
    </a>
</div>
@endsection
