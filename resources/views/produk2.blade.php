@extends('layouts.app')

@section('content')

<section class="bg-white py-12">
  <div class="max-w-6xl mx-auto px-4 grid grid-cols-1 md:grid-cols-2 gap-10">

    {{-- ========================= GALLERY / SLIDER ========================= --}}
    <div 
      x-data="{
        base: '{{ asset('storage') }}',
        paths: @js($images ?? []),
        main: '{{ $produk->gambar_produk }}',
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
      tabindex="0"
      class="focus:outline-none"
    >

      <!-- Area gambar utama -->
      <div class="relative bg-white border rounded h-72 md:h-96 flex items-center justify-center select-none">
        <template x-if="images.length > 1">
          <button type="button" @click="prev()"
                  class="absolute left-2 md:left-3 top-1/2 -translate-y-1/2 p-2 rounded-full bg-white/80 hover:bg-white shadow">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
          </button>
        </template>

        <template x-if="images.length">
          <img :src="url(idx)"
               alt="{{ addslashes($produk->nama_produk) }}"
               class="max-h-full max-w-full object-contain"
               loading="lazy">
        </template>
        <template x-if="!images.length">
          <div class="text-gray-400">Tidak ada gambar</div>
        </template>

        <template x-if="images.length > 1">
          <button type="button" @click="next()"
                  class="absolute right-2 md:right-3 top-1/2 -translate-y-1/2 p-2 rounded-full bg-white/80 hover:bg-white shadow">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
          </button>
        </template>

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
      @php
        $soldOut = (int) $produk->stok <= 0;
        // Prefer controller-provided sizeOptions; kalau tidak ada parse langsung
        if (isset($sizeOptions) && is_iterable($sizeOptions)) {
            $opts = collect($sizeOptions);
        } else {
            // parse ukuran_tersedia via helper (key => ['label','stock'])
            $map = \App\Support\SizeStock::parse((string)$produk->ukuran_tersedia);
            $opts = collect($map)->map(function($row, $key){
                return ['key'=>$key, 'label'=>$row['label'] ?? '', 'stock'=>$row['stock']];
            })->values();
        }

        // alreadyBoughtSizes mungkin dikirim dari controller; jika tidak, ambil empty collection
        $bought = collect($alreadyBoughtSizes ?? [])->map(fn($v)=>mb_strtolower($v));
      @endphp

      <form action="{{ route('cart.add', $produk->id_produk) }}" method="POST" class="space-y-4">
        @csrf

        <h2 class="text-2xl font-semibold">{{ $produk->nama_produk }}</h2>

        <p class="text-xl font-bold text-pink-600">
          Rp {{ number_format($produk->harga, 0, ',', '.') }}
        </p>

        <p class="text-gray-600">{{ $produk->deskripsi }}</p>
        {{-- ================== WARNA (pilihan warna) ================== --}}
<div>
  <p class="font-medium mb-1">Warna <span class="text-red-500">*</span></p>

  @php
    // Pastikan $produk->warna berupa string atau array yang aman
    $colors = [];

    if (!empty($produk->warna)) {
        if (is_array($produk->warna)) {
            // Jika sudah array
            $colors = array_filter($produk->warna);
        } elseif (is_string($produk->warna)) {
            // Jika string seperti "Merah, Biru, Hitam"
            $colors = array_filter(array_map('trim', explode(',', $produk->warna)));
        }
    }
  @endphp

  @if(count($colors) > 0)
    <div class="flex flex-wrap gap-2">
      @foreach($colors as $warna)
        <label class="cursor-pointer">
          <input type="radio" name="warna" value="{{ $warna }}"
                 class="peer sr-only"
                 required
                 {{ old('warna') === $warna ? 'checked' : '' }}>
          <span class="px-3 py-1 border rounded block peer-checked:bg-black peer-checked:text-white">
            {{ ucfirst($warna) }}
          </span>
        </label>
      @endforeach
    </div>
  @else
    <div class="text-sm text-gray-500 italic">Warna belum diatur untuk produk ini.</div>
  @endif

  @error('warna')
    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
  @enderror
</div>


        {{-- ================== UKURAN (single, non-duplicated) ================== --}}
        <div>
          <p class="font-medium mb-1">Ukuran <span class="text-red-500">*</span></p>

          @if($opts->isNotEmpty())
            <div class="flex flex-wrap gap-2">
              @foreach($opts as $opt)
                @php
                  $key = $opt['key'];
                  $label = $opt['label'] ?: strtoupper($key);
                  $stockPer = $opt['stock']; // null = unlimited per-size
                  $soldThis = $stockPer !== null && (int)$stockPer <= 0;
                  $already = $bought->contains(mb_strtolower($key));
                  $disabled = $soldOut || $soldThis || $already;
                @endphp

                @continue($already) {{-- sembunyikan ukuran yg sudah dibeli user (opsional) --}}

                <label class="cursor-pointer">
                  <input type="radio" name="ukuran" value="{{ $key }}" class="peer sr-only" required {{ $disabled ? 'disabled' : '' }} {{ old('ukuran') === $key ? 'checked' : '' }}>
                  <span class="px-3 py-1 border rounded block peer-checked:bg-black peer-checked:text-white {{ $disabled ? 'opacity-50 cursor-not-allowed' : '' }}">
                    {{ $label }}
                    @if($stockPer === null)
                      {{-- tidak tampilkan angka --}}
                    @elseif($soldThis)
                      (habis)
                    @else
                      ({{ $stockPer }})
                    @endif
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

        {{-- Jumlah (qty) --}}
        <div class="flex items-center gap-3">
          <div>
            {{-- <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah</label> --}}
            <div class="flex items-center ">
              <button type="button" id="qtyMinus" class="px-3 py-1 border rounded" hidden>-</button>
              <input id="qtyInput" name="jumlah" type="number" min="1" value="1" hidden
                     class="w-20 px-2 py-1 border rounded text-center" />
              <button type="button" id="qtyPlus" class="px-3 py-1 border rounded" hidden>+</button>
            </div>            
          </div>

          <div class="">
            <button type="submit"
                    {{ $soldOut ? 'disabled' : '' }}
                    class="px-6 py-2 rounded text-white {{ $soldOut ? 'bg-gray-400 cursor-not-allowed' : 'bg-black hover:bg-gray-800' }}">
              {{ $soldOut ? 'Stok Habis' : 'Beli' }}
            </button>
          </div>
        </div>

        <div>
          <a href="{{ route('cart.index') }}"
             class="p-2 border rounded hover:bg-gray-100"
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

{{-- Client-side qty controls + safety check --}}
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

