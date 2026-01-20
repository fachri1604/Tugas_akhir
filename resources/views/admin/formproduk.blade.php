{{-- resources/views/admin/formproduk.blade.php --}}
@extends('layouts.admin')

@section('content')
@php
  // Prefill editor ukuran dari old value atau produk
  $initialSizeMap = \App\Support\SizeStock::parse(old('ukuran_tersedia', $produk->ukuran_tersedia ?? ''));
  $initialRows = [];
  foreach ($initialSizeMap as $k => $row) {
      // Pastikan stock bernilai '' kalau null agar UI menampilkan kosong
      $initialRows[] = ['label' => $row['label'], 'stock' => is_null($row['stock']) ? '' : $row['stock']];
  }
@endphp

<div class="max-w-5xl mx-auto py-6 sm:px-6 lg:px-8">
  <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
    <div class="p-6 sm:p-8">
      <h1 class="text-2xl font-bold text-gray-800 mb-6">
        {{ isset($produk) ? 'Edit Produk' : 'Tambah Produk' }}
      </h1>

      @if ($errors->any())
        <div class="mb-6 p-4 bg-red-100 border-l-4 border-red-500 text-red-700 rounded">
          <ul class="list-disc list-inside text-sm">
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      {{-- gabungkan sizeEditor + multiImagePicker --}}
      <form method="POST"
            action="{{ isset($produk) ? route('admin.updateproduk', $produk->id_produk) : route('admin.produk.store') }}"
            enctype="multipart/form-data"
            x-data="Object.assign(sizeEditor(@js($initialRows)), multiImagePicker())"
            x-init="init()">
        @csrf
        @if(isset($produk)) @method('PUT') @endif

        {{-- Nama & Kategori --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Produk *</label>
            <input type="text" name="nama_produk" required
                   value="{{ old('nama_produk', $produk->nama_produk ?? '') }}"
                   class="w-full px-3 py-2 border rounded-md">
            @error('nama_produk') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Kategori *</label>
            <select name="kategori_id" required class="w-full px-3 py-2 border rounded-md">
              <option value="">-- Pilih --</option>
              @foreach($kategoris as $kat)
                <option value="{{ $kat->id }}" @selected(old('kategori_id', $produk->kategori_id ?? '') == $kat->id)>{{ $kat->nama_kategori }}</option>
              @endforeach
            </select>
            @error('kategori_id') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
          </div>
        </div>

        {{-- Deskripsi --}}
        <div class="mt-6">
          <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
          <textarea name="deskripsi" rows="4" class="w-full px-3 py-2 border rounded-md">{{ old('deskripsi', $produk->deskripsi ?? '') }}</textarea>
          @error('deskripsi') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Harga --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Harga (Rp) *</label>
            <input type="number" name="harga" min="0" step="1" required
                   value="{{ old('harga', $produk->harga ?? '') }}"
                   class="w-full px-3 py-2 border rounded-md">
            @error('harga') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
          </div>

          {{-- HIDDEN stok (otomatis diisi oleh Alpine) --}}
          <input type="hidden" name="stok" x-model="computedStock">
        </div>

        {{-- Warna + Ukuran --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Warna (pisahkan koma)</label>
            <input type="text" name="warna" value="{{ old('warna', $produk->warna ?? '') }}" class="w-full px-3 py-2 border rounded-md">
            @error('warna') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
          </div>

          <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Ukuran Tersedia (label + optional stok)</label>
            <p class="text-xs text-gray-500 mb-2">
              Masukkan ukuran. Bila Anda masukkan angka stok pada tiap baris, stok global akan dihitung sebagai jumlah semua angka stok.
              Jika tidak ada angka stok pada baris, tiap baris dianggap stok = 1.
            </p>

            <div class="space-y-2">
              <template x-for="(row, i) in rows" :key="i">
                <div class="flex gap-2 items-center">
                  <input type="text" x-model="row.label" placeholder="Ukuran (mis. S, M, 36, All Size)"
                         class="flex-1 px-3 py-2 border rounded-md" @input="rebuildString()">
                  <input type="number" x-model.number="row.stock" placeholder="Stok (opsional)"
                         class="w-32 px-3 py-2 border rounded-md" @input="coerce(i)">
                  <button type="button" @click="remove(i)" class="px-2 py-2 border rounded-md hover:bg-gray-50">Hapus</button>
                </div>
              </template>

              <div class="flex gap-2">
                <button type="button" @click="add(); rebuildString()" class="px-3 py-2 rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                  + Tambah Ukuran
                </button>
                <button type="button" @click="rebuildString()" class="px-3 py-2 rounded-md border hover:bg-gray-50">
                  Terapkan
                </button>
              </div>
            </div>

            {{-- ukuran_tersedia dikirim sebagai string --}}
            <input type="hidden" name="ukuran_tersedia" x-model="stringValue">

            <p class="text-xs text-gray-500 mt-2">
              Nilai yang tersimpan: <span class="font-mono" x-text="stringValue"></span>
              — Stok global terhitung: <strong x-text="computedStock"></strong>
            </p>

            @error('ukuran_tersedia') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
          </div>
        </div>

        {{-- Foto Multiple --}}
        <div class="mt-8">
          <label class="block text-sm font-medium text-gray-700 mb-1">Foto Produk (bisa banyak)</label>
          <input type="file" name="images[]" accept="image/*" multiple x-ref="picker" @change="onPick($event)" class="w-full px-3 py-2 border rounded-md">
          <p class="text-xs text-gray-500 mt-1">Format: jpg, jpeg, png, webp, gif, svg. Maks 4MB/foto.</p>
        </div>

        {{-- Foto lama saat edit --}}
        @if(isset($produk) && !empty($existingImages))
          <div class="mt-8 border-t pt-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-3">Foto tersimpan</h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
              @foreach($existingImages as $img)
                <div class="border rounded-md p-2 relative">
                  <img src="{{ asset('storage/'.$img) }}" class="w-full h-32 object-cover rounded">
                  <div class="mt-2 flex items-center justify-between text-xs">
                    <label class="inline-flex items-center gap-1">
                      <input type="radio" name="primary_path" value="{{ $img }}" {{ ($produk->gambar_produk ?? '') === $img ? 'checked' : '' }}>
                      <span>Jadikan utama</span>
                    </label>
                    <label class="inline-flex items-center gap-1 text-red-600">
                      <input type="checkbox" name="delete_paths[]" value="{{ $img }}">
                      <span>Hapus</span>
                    </label>
                  </div>
                  @if(($produk->gambar_produk ?? '') === $img)
                    <span class="absolute top-2 right-2 text-[10px] px-2 py-0.5 rounded-full bg-green-600 text-white">utama</span>
                  @endif
                </div>
              @endforeach
            </div>
          </div>
        @endif

        <div class="flex items-center justify-between mt-8 pt-6 border-t">
          <a href="{{ route('admin.produk') }}" class="px-4 py-2 border rounded-md bg-white hover:bg-gray-50">Kembali</a>
          <button type="submit" class="px-5 py-2.5 rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
            {{ isset($produk) ? 'Simpan Perubahan' : 'Simpan Produk' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- Alpine helpers --}}
<script>
function sizeEditor(initialRows = []) {
  return {
    rows: (initialRows && initialRows.length) ? initialRows.map(r=>({label:r.label, stock: r.stock})) : [],
    stringValue: '',
    computedStock: 0, // stok global yang dihitung

    init() {
      // bangun stringValue & computedStock dari rows jika ada
      if (this.rows && this.rows.length) {
        this.rebuildString();
        return;
      }
      // default minimal baris agar UI tidak kosong
      this.rows = [{label:'S', stock: ''}, {label:'M', stock: ''}];
      this.rebuildString();
    },

    add() {
      this.rows.push({ label: '', stock: '' });
      this.rebuildString();
    },

    remove(i) {
      this.rows.splice(i, 1);
      this.rebuildString();
    },

    coerce(i) {
      let v = this.rows[i].stock;
      if (v === '' || v === null || typeof v === 'undefined') {
        this.rows[i].stock = '';
      } else {
        this.rows[i].stock = parseInt(v, 10) || 0;
      }
      this.rebuildString();
    },

    rebuildString() {
      const out = [];
      let sum = 0;
      this.rows.forEach(r => {
        const label = (r.label || '').toString().trim();
        if (!label) return;
        if (r.stock === '' || r.stock === null || typeof r.stock === 'undefined') {
          out.push(label);
          sum += 1; // default 1 bila stock tidak diberikan
        } else {
          const n = parseInt(r.stock, 10) || 0;
          out.push(label + ':' + n);
          sum += n;
        }
      });
      this.stringValue = out.join(', ');
      this.computedStock = sum;
    }
  };
}

function multiImagePicker() {
  return {
    maxFiles: 6,
    previews: [],
    files: [],
    hiddenCount: 0,
    primary: 0,
    onPick(e) {
      this.previews = []; this.files = []; this.hiddenCount = 0; this.primary = 0;
      const picked = Array.from(e.target.files || []);
      if (!picked.length) return;
      this.hiddenCount = Math.max(0, picked.length - this.maxFiles);
      this.files = picked.slice(0, this.maxFiles);
      this.files.forEach((f) => {
        const rd = new FileReader();
        rd.onload = (ev) => this.previews.push(ev.target.result);
        rd.readAsDataURL(f);
      });
    },
    removeFile(idx) {
      this.files.splice(idx, 1);
      this.previews.splice(idx, 1);
      const dt = new DataTransfer();
      const originals = Array.from(this.$refs.picker.files || []);
      let kept = originals.filter((_, i) => i !== idx);
      kept.forEach(f => dt.items.add(f));
      this.$refs.picker.files = dt.files;
      if (this.primary === idx) this.primary = 0;
      if (this.primary > this.previews.length - 1) this.primary = 0;
    }
  };
}
</script>
@endsection
