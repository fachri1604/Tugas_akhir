@extends('layouts.admin')

@section('content')
<div class="p-6 max-w-3xl mx-auto">
    <h1 class="text-2xl font-bold mb-6 text-gray-800">Tambah / Kurangi Stok</h1>

    <form method="POST" action="{{ route('admin.stok.store') }}" id="stokForm">
        @csrf

        {{-- Pilih produk --}}
        <div class="mb-4">
            <label class="block font-medium mb-1">Produk</label>
            <select name="produk_id" class="w-full border rounded px-3 py-2">
                <option value="">-- Pilih Produk --</option>
                @foreach($produks as $produk)
                    <option value="{{ $produk->id_produk }}">{{ $produk->nama_produk }}</option>
                @endforeach
            </select>
        </div>

        {{-- Daftar ukuran --}}
        <div id="ukuran-container">
            <div class="ukuran-group flex items-center gap-2 mb-2">
                <select class="ukuran border rounded px-2 py-1 w-1/3">
                    <option value="">-- Pilih Ukuran --</option>
                    <option value="S">S</option>
                    <option value="M">M</option>
                    <option value="L">L</option>
                    <option value="XL">XL</option>
                    <option value="XXL">XXL</option>
                </select>
                <input type="text" class="ukuran-baru border rounded px-2 py-1 w-1/3" placeholder="Ukuran baru (opsional)">
                <input type="number" class="jumlah border rounded px-2 py-1 w-1/4" placeholder="Jumlah" min="1">
                <button type="button" class="hapus-ukuran text-red-500 font-bold">×</button>
            </div>
        </div>

        <button type="button" id="tambah-ukuran" class="bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded mb-4">
            + Tambah Ukuran
        </button>

        {{-- Tipe stok --}}
        <div class="mb-4">
            <label class="block font-medium mb-1">Tipe</label>
            <select name="tipe" class="w-full border rounded px-3 py-2">
                <option value="masuk">Masuk</option>
                <option value="keluar">Keluar</option>
            </select>
        </div>

        {{-- Catatan --}}
        <div class="mb-4">
            <label class="block font-medium mb-1">Catatan (opsional)</label>
            <textarea name="catatan" class="w-full border rounded px-3 py-2" rows="2"></textarea>
        </div>

        <input type="hidden" name="ukuran_data" id="ukuran_data">

        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Simpan</button>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('ukuran-container');
    const tambahBtn = document.getElementById('tambah-ukuran');
    const form = document.getElementById('stokForm');

    tambahBtn.addEventListener('click', () => {
        // Ambil semua ukuran yang sudah dipilih
        const existingSizes = Array.from(container.querySelectorAll('.ukuran')).map(s => s.value.toUpperCase());

        const newRow = document.createElement('div');
        newRow.classList.add('ukuran-group', 'flex', 'items-center', 'gap-2', 'mb-2');
        newRow.innerHTML = `
            <select class="ukuran border rounded px-2 py-1 w-1/3">
                <option value="">-- Pilih Ukuran --</option>
                <option value="S">S</option>
                <option value="M">M</option>
                <option value="L">L</option>
                <option value="XL">XL</option>
                <option value="XXL">XXL</option>
            </select>
            <input type="text" class="ukuran-baru border rounded px-2 py-1 w-1/3" placeholder="Ukuran baru (opsional)">
            <input type="number" class="jumlah border rounded px-2 py-1 w-1/4" placeholder="Jumlah" min="1">
            <button type="button" class="hapus-ukuran text-red-500 font-bold">×</button>
        `;

        const ukuranSelect = newRow.querySelector('.ukuran');

        // Jika ukuran sudah pernah dipilih → tampilkan alert
        ukuranSelect.addEventListener('change', (e) => {
            const val = e.target.value.toUpperCase();
            if (existingSizes.includes(val)) {
                alert(`Ukuran "${val}" sudah dipilih sebelumnya!`);
                e.target.value = "";
            }
        });

        container.appendChild(newRow);

        // Tombol hapus ukuran
        newRow.querySelector('.hapus-ukuran').addEventListener('click', () => {
            newRow.remove();
        });
    });

    // Saat submit → kumpulkan semua ukuran dan jumlah ke JSON
    form.addEventListener('submit', (e) => {
        const data = [];
        container.querySelectorAll('.ukuran-group').forEach(group => {
            const ukuran = group.querySelector('.ukuran').value.trim();
            const ukuranBaru = group.querySelector('.ukuran-baru').value.trim();
            const jumlah = group.querySelector('.jumlah').value.trim();
            if (ukuran || ukuranBaru) {
                data.push({ ukuran, ukuran_baru: ukuranBaru, jumlah });
            }
        });
        document.getElementById('ukuran_data').value = JSON.stringify(data);
    });
});
</script>
@endsection
