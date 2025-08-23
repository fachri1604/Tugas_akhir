@extends('layouts.app')

@section('content')

<section class="bg-white py-12">
  <div class="max-w-6xl mx-auto px-4 grid grid-cols-1 md:grid-cols-2 gap-10">

    {{-- ========================= GALLERY / SLIDER ========================= --}}
    <div 
      x-data="{
        base: '{{ asset('storage') }}',
        paths: @js($images ?? []),             // array path relatif dari Storage::disk('public')
        main: '{{ $produk->gambar_produk }}',  // fallback jika $images kosong
        get images() {
          if (this.paths && this.paths.length) return this.paths;
          return this.main ? [this.main] : [];
        },
        idx: 0,
        url(i) {
          const p = this.images[i] || '';
          return p.startsWith('http') ? p : (this.base + '/' + p.replace(/^\/+/, ''));
        },
        next() { if (this.images.length) this.idx = (this.idx + 1) % this.images.length },
        prev() { if (this.images.length) this.idx = (this.idx - 1 + this.images.length) % this.images.length },
        autoplay: null,
        startAuto() {
          if (this.images.length <= 1) return;
          this.stopAuto();
          this.autoplay = setInterval(() => this.next(), 4000);
        },
        stopAuto() {
          if (this.autoplay) { clearInterval(this.autoplay); this.autoplay = null; }
        }
      }"
      x-init="startAuto()"
      @mouseenter="stopAuto()"
      @mouseleave="startAuto()"
      @keydown.arrow-right.prevent="next()"
      @keydown.arrow-left.prevent="prev()"
      tabindex="0"
      class="focus:outline-none"
    >

      <!-- Area gambar utama -->
      <div class="relative bg-white border rounded h-72 md:h-96 flex items-center justify-center select-none">

        <!-- Panah kiri -->
        <template x-if="images.length > 1">
          <button type="button" @click="prev()"
                  class="absolute left-2 md:left-3 top-1/2 -translate-y-1/2 p-2 rounded-full bg-white/80 hover:bg-white shadow">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
          </button>
        </template>

        <!-- Gambar -->
        <template x-if="images.length">
          <img :src="url(idx)"
               alt="{{ addslashes($produk->nama_produk) }}"
               class="max-h-full max-w-full object-contain"
               loading="lazy">
        </template>
        <template x-if="!images.length">
          <div class="text-gray-400">Tidak ada gambar</div>
        </template>

        <!-- Panah kanan -->
        <template x-if="images.length > 1">
          <button type="button" @click="next()"
                  class="absolute right-2 md:right-3 top-1/2 -translate-y-1/2 p-2 rounded-full bg-white/80 hover:bg-white shadow">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
          </button>
        </template>

        <!-- Dots indikator -->
        <template x-if="images.length > 1">
          <div class="absolute bottom-2 left-1/2 -translate-x-1/2 flex gap-1.5">
            <template x-for="(p, i) in images" :key="i">
              <button type="button" @click="idx = i"
                      class="w-2.5 h-2.5 rounded-full"
                      :class="i === idx ? 'bg-gray-800' : 'bg-gray-300'"></button>
            </template>
          </div>
        </template>
      </div>

      <!-- Thumbnails -->
      <template x-if="images.length > 1">
        <div class="mt-3 grid grid-cols-5 sm:grid-cols-6 md:grid-cols-7 lg:grid-cols-8 gap-2">
          <template x-for="(p, i) in images" :key="i">
            <button type="button" @click="idx = i"
                    class="border rounded overflow-hidden aspect-square relative group"
                    :class="i === idx ? 'ring-2 ring-black' : ''">
              <img :src="url(i)" alt="" class="w-full h-full object-cover transition-transform group-hover:scale-105" loading="lazy">
            </button>
          </template>
        </div>
      </template>
    </div>
    {{-- ======================= /GALLERY / SLIDER ======================= --}}

    {{-- ======================= DETAIL + FORM ======================= --}}
    <div>
      @php $soldOut = (int) $produk->stok <= 0; @endphp

      <form action="{{ route('cart.add', $produk->id_produk) }}" method="POST" class="space-y-4">
        @csrf

        <h2 class="text-2xl font-semibold">{{ $produk->nama_produk }}</h2>

        <p class="text-xl font-bold text-pink-600">
          Rp {{ number_format($produk->harga, 0, ',', '.') }}
        </p>

        <p class="text-gray-600">{{ $produk->deskripsi }}</p>

        {{-- ================== UKURAN ================== --}}
        @php
          $sizesRaw = (string) $produk->ukuran_tersedia;
          $sizes = collect(preg_split('/\s*[,;|\/]\s*/', $sizesRaw))
              ->map(fn($v) => trim($v))
              ->filter()
              ->map(function ($v) {
                  $lower = mb_strtolower($v);
                  if (in_array($lower, ['allsize','all size','all-size','all sz','all'])) return 'All Size';
                  return preg_match('/^\d+$/', $v) ? $v : strtoupper($v);
              })
              ->unique()->values();

          $sizeOptions = $sizes->map(fn($label) => [
              'key'   => trim(mb_strtolower($label)),
              'label' => $label
          ]);
        @endphp

        <div>
          <p class="font-medium mb-1">Ukuran <span class="text-red-500">*</span></p>
          @if($sizeOptions->isNotEmpty())
            <div class="flex flex-wrap gap-2">
              @foreach($sizeOptions as $opt)
                <label class="cursor-pointer">
                  <input
                    type="radio" name="ukuran" value="{{ $opt['key'] }}"
                    class="peer sr-only"
                    @if($loop->first) required @endif
                    {{ $soldOut ? 'disabled' : '' }}
                    {{ old('ukuran') === $opt['key'] ? 'checked' : '' }}>
                  <span class="px-3 py-1 border rounded block peer-checked:bg-black peer-checked:text-white {{ $soldOut ? 'opacity-50 cursor-not-allowed' : '' }}">
                    {{ $opt['label'] }}
                  </span>
                </label>
              @endforeach
            </div>
          @else
            <div class="text-sm text-gray-500 italic">Ukuran belum diatur untuk produk ini.</div>
          @endif
          @error('ukuran')
            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
          @enderror
        </div>

        {{-- ================== WARNA ================== --}}
        {{-- @php
          $warnaLabels = collect(preg_split('/\s*[,;|\/]\s*/', (string) $produk->warna))
              ->map(fn($v) => trim($v))->filter()->unique()->values();

          $colorMap = [
            'hitam'=>'#000000','black'=>'#000000','putih'=>'#ffffff','white'=>'#ffffff',
            'merah'=>'#ff0000','red'=>'#ff0000','biru'=>'#0000ff','blue'=>'#0000ff',
            'kuning'=>'#ffff00','yellow'=>'#ffff00','hijau'=>'#00a650','green'=>'#008000',
            'abu'=>'#808080','abu-abu'=>'#808080','grey'=>'#808080','gray'=>'#808080',
            'coklat'=>'#8B4513','brown'=>'#8B4513','pink'=>'#ff69b4','ungu'=>'#800080','purple'=>'#800080',
            'oranye'=>'#ffa500','orange'=>'#ffa500','cream'=>'#f5f5dc','krim'=>'#f5f5dc',
            'navy'=>'#000080','maroon'=>'#800000','khaki'=>'#f0e68c',
          ];

          $warnaOptions = $warnaLabels->map(function ($label) use ($colorMap) {
              $key = mb_strtolower($label);
              $hex = $colorMap[$key] ?? (preg_match('/^#([0-9a-f]{3}|[0-9a-f]{6})$/i', $label) ? $label : null);
              return ['key' => $key, 'label' => $label, 'hex' => $hex];
          });
        @endphp

        @if($warnaOptions->count())
          <div>
            <p class="font-medium mb-1">Warna <span class="text-red-500">*</span></p>
            <div class="flex flex-wrap gap-2">
              @foreach($warnaOptions as $opt)
                <label class="cursor-pointer">
                  <input
                    type="radio" name="warna" value="{{ $opt['key'] }}"
                    class="peer sr-only"
                    @if($loop->first) required @endif
                    {{ $soldOut ? 'disabled' : '' }}
                    {{ old('warna') === $opt['key'] ? 'checked' : '' }}>
                  <span class="px-3 py-1 border rounded flex items-center gap-2 peer-checked:bg-pink-600 peer-checked:text-white {{ $soldOut ? 'opacity-50 cursor-not-allowed' : '' }}">
                    @if($opt['hex'])
                      <span class="inline-block w-4 h-4 rounded-full border" style="background-color: {{ $opt['hex'] }}"></span>
                    @endif
                    <span>{{ $opt['label'] }}</span>
                  </span>
                </label>
              @endforeach
            </div>
            @error('warna')
              <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
            @enderror
          </div>
        @endif --}}

        {{-- Stok & Jumlah --}}
        <div class="flex items-center gap-4">
          @if(!$soldOut)
            <p>Stok : <span class="font-medium">{{ $produk->stok }}</span></p>
            <div class="flex items-center border rounded overflow-hidden">
              <button type="button" class="px-3 py-1 bg-gray-200 hover:bg-gray-300" id="qtyMinus">-</button>
              <input type="number" name="jumlah" id="qtyInput"
                     value="{{ old('jumlah', 1) }}" min="1" max="{{ $produk->stok }}"
                     class="w-14 text-center border-x outline-none">
              <button type="button" class="px-3 py-1 bg-gray-200 hover:bg-gray-300" id="qtyPlus">+</button>
            </div>
          @else
            <p class="text-red-600 font-semibold">Stok : 0 (Habis)</p>
          @endif
        </div>

        {{-- Tombol --}}
        <div class="flex items-center gap-3 mt-2">
          <button type="submit"
                  {{ $soldOut ? 'disabled' : '' }}
                  class="px-6 py-2 rounded text-white {{ $soldOut ? 'bg-gray-400 cursor-not-allowed' : 'bg-black hover:bg-gray-800' }}">
            {{ $soldOut ? 'Stok Habis' : 'Beli' }}
          </button>

          <a href="{{ route('cart.index') }}"
             class="p-2 border rounded hover:bg-gray-100 {{ $soldOut ? 'pointer-events-none opacity-50' : '' }}"
             title="Lihat Keranjang">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-700" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor">
              <path d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-1.2 6M17 13l1.2 6M6 19a2 2 0 100 4 2 2 0 000-4z" />
            </svg>
          </a>
        </div>
      </form>
    </div>
    {{-- ======================= /DETAIL + FORM ======================= --}}

  </div>
</section>

@if(!(int) $produk->stok <= 0)
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
@endif

@endsection
