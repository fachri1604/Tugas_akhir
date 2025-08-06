@extends('layouts.admin')

@section('content')
<div class="max-w-4xl mx-auto mt-10 p-6 bg-white rounded-lg shadow" x-data="stokForm()">
    <h1 class="text-xl font-bold mb-4">Tambah Stok Produk</h1>
    
    <form action="{{ route('admin.stok.store') }}" method="POST">
        @csrf

        <!-- Produk -->
        <label for="produk_id">Pilih Produk:</label>        
        <select name="produk_id" class="border p-2 rounded w-full mb-4" required>
            <option value="">-- Pilih Produk --</option>
            @foreach($produks as $produk)
                <option value="{{ $produk->id }}">{{ $produk->nama_produk }}</option>
            @endforeach
        </select>

        <!-- Dinamis Input Warna + Ukuran + Jumlah -->
        <template x-for="(item, index) in kombinasi" :key="index">
            <div class="mb-4 flex gap-2">
                <select :name="`kombinasi[${index}][warna]`" class="border p-2 rounded" required>
                    <option value="">Pilih Warna</option>
                    @foreach($warnas as $warna)
                        <option value="{{ $warna }}">{{ $warna }}</option>
                    @endforeach
                </select>

                <select :name="`kombinasi[${index}][ukuran]`" class="border p-2 rounded" required>
                    <option value="">Pilih Ukuran</option>
                    @foreach($ukurans as $ukuran)
                        <option value="{{ $ukuran }}">{{ $ukuran }}</option>
                    @endforeach
                </select>

                <input type="number" :name="`kombinasi[${index}][jumlah]`" class="border p-2 rounded w-24" min="1" required placeholder="Jumlah">

                <button type="button" @click="hapus(index)" class="text-red-500">Hapus</button>
            </div>
        </template>

        <button type="button" @click="tambah()" class="bg-blue-500 text-white px-4 py-2 rounded mb-4">+ Tambah Kombinasi</button>

        <!-- Tambahan -->
        <label for="tipe">Tipe:</label>
        <select name="tipe" class="border p-2 rounded mb-2 w-full" required>
            <option value="tambah">Tambah</option>
            <option value="kurang">Kurang</option>
        </select>

        <label for="catatan">Catatan (opsional):</label>
        <input type="text" name="catatan" class="border p-2 rounded w-full mb-2">

        <label for="alamat">Alamat (opsional):</label>
        <input type="text" name="alamat" class="border p-2 rounded w-full mb-4">

        <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded">Simpan</button>
    </form>
</div>

<script>
    function stokForm() {
        return {
            kombinasi: [
                { warna: '', ukuran: '', jumlah: 1 }
            ],
            tambah() {
                this.kombinasi.push({ warna: '', ukuran: '', jumlah: 1 });
            },
            hapus(index) {
                this.kombinasi.splice(index, 1);
            }
        }
    }
</script>
@endsection
