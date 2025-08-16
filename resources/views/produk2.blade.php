@extends('layouts.app')

@section('content')

<section class="bg-white py-12">
  <div class="max-w-6xl mx-auto px-4 grid grid-cols-1 md:grid-cols-2 gap-10">

    {{-- Gambar --}}
    <div>
      <div class="flex items-center justify-center bg-white border rounded h-72 md:h-96">
        <img src="{{ asset('storage/' . $produk->gambar_produk) }}"
             alt="{{ $produk->nama_produk }}"
             class="max-h-full max-w-full object-contain">
      </div>
    </div>

    {{-- Detail + FORM --}}
    <div>
      <form action="{{ route('cart.add', $produk->id_produk) }}" method="POST" class="space-y-4">
        @csrf

        <h2 class="text-2xl font-semibold">{{ $produk->nama_produk }}</h2>

        <p class="text-xl font-bold text-pink-600">
          Rp {{ number_format($produk->harga, 0, ',', '.') }}
        </p>

        <p class="text-gray-600">{{ $produk->deskripsi }}</p>

        {{-- Ukuran (radio) --}}
        <div>
          <p class="font-medium mb-1">Ukuran <span class="text-red-500">*</span></p>
          <div class="flex flex-wrap gap-2">
            @foreach(['S','M','L','XL','XXL'] as $size)
              <label class="cursor-pointer">
                <input type="radio" name="ukuran" value="{{ $size }}" class="peer sr-only" required>
                <span class="px-3 py-1 border rounded block peer-checked:bg-black peer-checked:text-white">
                  {{ $size }}
                </span>
              </label>
            @endforeach
          </div>
          @error('ukuran')
            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
          @enderror
        </div>

        {{-- Warna (radio dari database) --}}
        @php
          $warnaArray = collect(explode(',', (string) $produk->warna))
                          ->map(fn($v) => trim($v))
                          ->filter()
                          ->values();

          // Peta nama warna (ID/EN) -> hex untuk preview bulatan warna
          $colorMap = [
            'hitam' => '#000000', 'black' => '#000000',
            'putih' => '#ffffff', 'white' => '#ffffff',
            'merah' => '#ff0000', 'red'   => '#ff0000',
            'biru'  => '#0000ff', 'blue'  => '#0000ff',
            'kuning'=> '#ffff00', 'yellow'=> '#ffff00',
            'hijau' => '#00a650', 'green' => '#008000',
            'abu'   => '#808080', 'abu-abu' => '#808080', 'grey'=>'#808080', 'gray'=>'#808080',
            'coklat'=> '#8B4513', 'brown' => '#8B4513',
            'pink'  => '#ff69b4',
            'ungu'  => '#800080', 'purple'=> '#800080',
            'oranye'=> '#ffa500', 'orange'=> '#ffa500',
            'cream' => '#f5f5dc', 'krim' => '#f5f5dc',
            'navy'  => '#000080',
            'maroon'=> '#800000',
            'khaki' => '#f0e68c',
          ];

          $warnaItems = $warnaArray->map(function ($w) use ($colorMap) {
              $key = strtolower($w);
              $hex = $colorMap[$key] ?? (preg_match('/^#([0-9a-f]{3}|[0-9a-f]{6})$/i', $w) ? $w : null);
              return ['label' => $w, 'hex' => $hex];
          });
        @endphp

        @if($warnaItems->count())
          <div>
            <p class="font-medium mb-1">Warna <span class="text-red-500">*</span></p>
            <div class="flex flex-wrap gap-2">
              @foreach($warnaItems as $item)
                <label class="cursor-pointer">
                  <input type="radio" name="warna" value="{{ $item['label'] }}" class="peer sr-only" required>
                  <span class="px-3 py-1 border rounded flex items-center gap-2 peer-checked:bg-pink-600 peer-checked:text-white">
                    @if($item['hex'])
                      <span class="inline-block w-4 h-4 rounded-full border" style="background-color: {{ $item['hex'] }}"></span>
                    @endif
                    <span>{{ $item['label'] }}</span>
                  </span>
                </label>
              @endforeach
            </div>
            @error('warna')
              <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
            @enderror
          </div>
        @endif

        {{-- Stok & Jumlah --}}
        <div class="flex items-center gap-4">
          <p>Stok : <span class="font-medium">{{ $produk->stok }}</span></p>
          <div class="flex items-center border rounded overflow-hidden">
            <button type="button" class="px-3 py-1 bg-gray-200 hover:bg-gray-300" id="qtyMinus">-</button>
            <input type="number" name="jumlah" id="qtyInput"
                   value="1" min="1" max="{{ $produk->stok }}"
                   class="w-14 text-center border-x outline-none">
            <button type="button" class="px-3 py-1 bg-gray-200 hover:bg-gray-300" id="qtyPlus">+</button>
          </div>
        </div>

        {{-- Tombol --}}
        <div class="flex items-center gap-3 mt-2">
          <button type="submit" class="bg-black text-white px-6 py-2 rounded hover:bg-gray-800">
            Beli
          </button>

          <a href="{{ route('cart.index') }}" class="p-2 border rounded hover:bg-gray-100" title="Lihat Keranjang">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-700" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor">
              <path d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-1.2 6M17 13l1.2 6M6 19a2 2 0 100 4 2 2 0 000-4z" />
            </svg>
          </a>
        </div>
      </form>
    </div>
  </div>
</section>

{{-- Qty controls --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
  const minus = document.getElementById('qtyMinus');
  const plus  = document.getElementById('qtyPlus');
  const input = document.getElementById('qtyInput');

  minus?.addEventListener('click', () => {
    const min = parseInt(input.min || '1', 10);
    let val = parseInt(input.value || '1', 10);
    if (val > min) input.value = val - 1;
  });

  plus?.addEventListener('click', () => {
    const max = parseInt(input.max || '9999', 10);
    let val = parseInt(input.value || '1', 10);
    if (val < max) input.value = val + 1;
  });

  input?.addEventListener('input', () => {
    const min = parseInt(input.min || '1', 10);
    const max = parseInt(input.max || '9999', 10);
    let val = parseInt(input.value || '1', 10);
    if (isNaN(val) || val < min) val = min;
    if (val > max) val = max;
    input.value = val;
  });
});
</script>

@endsection
