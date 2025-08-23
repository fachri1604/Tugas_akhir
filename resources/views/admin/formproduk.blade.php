{{-- resources/views/admin/formproduk.blade.php --}}
@extends('layouts.admin')

@section('content')
<div class="max-w-5xl mx-auto py-6 sm:px-6 lg:px-8" x-data="multiImagePicker()">
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

      <form method="POST"
            action="{{ isset($produk) ? route('admin.updateproduk', $produk->id_produk) : route('admin.produk.store') }}"
            enctype="multipart/form-data">
        @csrf
        @if(isset($produk)) @method('PUT') @endif

        {{-- Nama & Kategori --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Produk *</label>
            <input type="text" name="nama_produk" required
                   value="{{ old('nama_produk', $produk->nama_produk ?? '') }}"
                   class="w-full px-3 py-2 border rounded-md focus:ring-indigo-500 focus:border-indigo-500">
            @error('nama_produk') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Kategori *</label>
            <select name="kategori_id" required
                    class="w-full px-3 py-2 border rounded-md focus:ring-indigo-500 focus:border-indigo-500">
              <option value="">-- Pilih --</option>
              @foreach($kategoris as $kat)
                <option value="{{ $kat->id }}"
                        @selected(old('kategori_id', $produk->kategori_id ?? '') == $kat->id)>{{ $kat->nama_kategori }}</option>
              @endforeach
            </select>
            @error('kategori_id') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
          </div>
        </div>

        {{-- Deskripsi --}}
        <div class="mt-6">
          <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
          <textarea name="deskripsi" rows="4"
                    class="w-full px-3 py-2 border rounded-md focus:ring-indigo-500 focus:border-indigo-500">{{ old('deskripsi', $produk->deskripsi ?? '') }}</textarea>
          @error('deskripsi') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Harga & Stok --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Harga (Rp) *</label>
            <input type="number" name="harga" min="0" step="1" required
                   value="{{ old('harga', $produk->harga ?? '') }}"
                   class="w-full px-3 py-2 border rounded-md focus:ring-indigo-500 focus:border-indigo-500">
            @error('harga') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Stok</label>
            <input type="number" name="stok" min="0" step="1"
                   value={{ old('stok', $produk->stok ?? 0) }}
                   class="w-full px-3 py-2 border rounded-md focus:ring-indigo-500 focus:border-indigo-500">
            @error('stok') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
          </div>
        </div>

        {{-- Warna, Ukuran, Alamat --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Warna (pisahkan koma)</label>
            <input type="text" name="warna" value="{{ old('warna', $produk->warna ?? '') }}"
                   class="w-full px-3 py-2 border rounded-md focus:ring-indigo-500 focus:border-indigo-500">
            @error('warna') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Ukuran (pisahkan koma)</label>
            <input type="text" name="ukuran_tersedia" value="{{ old('ukuran_tersedia', $produk->ukuran_tersedia ?? '') }}"
                   class="w-full px-3 py-2 border rounded-md focus:ring-indigo-500 focus:border-indigo-500">
            @error('ukuran_tersedia') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Alamat</label>
            <input type="text" name="alamat" value="{{ old('alamat', $produk->alamat ?? '') }}"
                   class="w-full px-3 py-2 border rounded-md focus:ring-indigo-500 focus:border-indigo-500">
            @error('alamat') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
          </div>
        </div>

        {{-- ==== SATU INPUT SAJA: FOTO MULTIPLE ==== --}}
        <div class="mt-8">
          <label class="block text-sm font-medium text-gray-700 mb-1">Foto Produk (bisa banyak)</label>
          <input type="file"
                 name="images[]"
                 accept="image/*"
                 multiple
                 x-ref="picker"
                 @change="onPick($event)"
                 class="w-full px-3 py-2 border rounded-md focus:ring-indigo-500 focus:border-indigo-500">
          <p class="text-xs text-gray-500 mt-1">Format: jpg, jpeg, png, webp, gif, svg. Maks 4MB/foto. Maks 6 foto pratinjau.</p>
          @error('images') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
          @error('images.*') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror

          {{-- PREVIEW (SATU BLOK SAJA) --}}
          {{-- <template x-if="previews.length">
            <div class="mt-3">
              <p class="text-xs text-gray-500 mb-2">Pratinjau (pilih salah satu sebagai foto utama):</p>
              <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-2">
                <template x-for="(src, idx) in previews" :key="idx">
                  <div class="relative group">
                    <img :src="src" class="w-28 h-28 object-cover rounded-md border">
                    <label class="absolute top-1 left-1 bg-white/85 rounded px-1 text-[10px] cursor-pointer shadow">
                      <input type="radio" name="primary_index" :value="idx" :checked="idx === primary">
                      utama
                    </label>
                    <button type="button"
                            @click="remove(idx)"
                            class="hidden group-hover:block absolute -top-2 -right-2 bg-white border rounded-full w-6 h-6 text-xs shadow">
                      ✕
                    </button>
                  </div>
                </template>

                <template x-if="hiddenCount > 0">
                  <div class="w-28 h-28 flex items-center justify-center border rounded-md bg-gray-50 text-sm text-gray-600">
                    +<span x-text="hiddenCount"></span>
                  </div>
                </template>
              </div>
            </div>
          </template> --}}
        </div>

        {{-- Saat EDIT: tampilkan foto lama (set utama / hapus) --}}
        @if(isset($produk) && !empty($existingImages))
          <div class="mt-8 border-t pt-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-3">Foto tersimpan</h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
              @foreach($existingImages as $img)
                <div class="border rounded-md p-2 relative">
                  <img src="{{ asset('storage/'.$img) }}" class="w-full h-32 object-cover rounded">
                  <div class="mt-2 flex items-center justify-between text-xs">
                    <label class="inline-flex items-center gap-1">
                      <input type="radio" name="primary_path" value="{{ $img }}"
                             {{ ($produk->gambar_produk ?? '') === $img ? 'checked' : '' }}>
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
function multiImagePicker() {
  return {
    maxFiles: 6,        // batasi pratinjau agar UI tidak “berlebihan”
    previews: [],       // dataURL utk pratinjau
    files: [],          // daftar File yang dipratinjau
    hiddenCount: 0,     // jumlah file di atas batas pratinjau
    primary: 0,         // index foto utama default

    onPick(e) {
      // reset setiap kali memilih ulang
      this.previews = [];
      this.files = [];
      this.hiddenCount = 0;
      this.primary = 0;

      const picked = Array.from(e.target.files || []);
      if (!picked.length) return;

      // hitung sisanya bila melebihi batas pratinjau
      this.hiddenCount = Math.max(0, picked.length - this.maxFiles);

      // ambil maksimal maxFiles untuk pratinjau
      this.files = picked.slice(0, this.maxFiles);

      // buat dataURL pratinjau
      this.files.forEach((f) => {
        const rd = new FileReader();
        rd.onload = (ev) => this.previews.push(ev.target.result);
        rd.readAsDataURL(f);
      });

      // catatan: file yang tidak dipratinjau (karena > maxFiles) tetap terkirim via input,
      // karena kita tidak memodifikasi FileList asli (kecuali user menghapus manual via tombol ✕).
    },

    remove(idx) {
      // hapus dari pratinjau & files
      this.files.splice(idx, 1);
      this.previews.splice(idx, 1);

      // rebuild FileList pada input agar sinkron
      const dt = new DataTransfer();
      // ambil file dari input asli
      const originals = Array.from(this.$refs.picker.files || []);
      // buang item pada index yang dihapus (berdasarkan urutan pratinjau)
      let kept = originals.filter((_, i) => i !== idx);
      kept.forEach(f => dt.items.add(f));
      this.$refs.picker.files = dt.files;

      // perbaiki index primary
      if (this.primary === idx) this.primary = 0;
      if (this.primary > this.previews.length - 1) this.primary = 0;
    }
  }
}
</script>
@endsection
