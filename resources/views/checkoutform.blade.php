@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto py-12">
  <h2 class="text-2xl font-semibold mb-6">Form Checkout</h2>

  @if(session('error'))
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded mb-4">
      {{ session('error') }}
    </div>
  @endif

  @if(isset($api_error))
    <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-2 rounded mb-4">
      {{ $api_error }}
    </div>
  @endif

  @php
    // default supaya tidak error saat $pesanan null
    $productSubtotal = 0;
  @endphp

  @if($pesanan)
    @php
      foreach ($pesanan->detailPesanans as $d) {
          $productSubtotal += (int) $d->produk->harga * (int) $d->jumlah;
      }
    @endphp

    <form action="{{ route('checkout.process', $pesanan->id_pesanan) }}" method="POST" id="checkoutForm">
      @csrf

      {{-- Alamat --}}
      <div class="mb-4">
        <label class="block mb-1 font-medium">Alamat Lengkap</label>
        <textarea name="alamat" class="w-full border rounded p-2" required>{{ old('alamat', Auth::user()->alamat ?? '') }}</textarea>
      </div>

      {{-- Provinsi --}}
      <div class="mb-4">
        <label class="block mb-1 font-medium">Provinsi Tujuan</label>
        <select id="province" name="provinsi" class="w-full border rounded p-2" required>
          <option value="">-- Pilih Provinsi --</option>
          @foreach($provinces as $province)
            <option value="{{ $province['id'] }}">{{ $province['name'] }}</option>
          @endforeach
        </select>
      </div>

      {{-- Kota --}}
      <div class="mb-4">
        <label class="block mb-1 font-medium">Kota/Kabupaten Tujuan</label>
        <select id="city" name="kota" class="w-full border rounded p-2" required>
          <option value="">-- Pilih Kota --</option>
          @if(!empty($cities))
            @foreach($cities as $city)
              <option value="{{ $city['id'] }}">{{ $city['name'] }}</option>
            @endforeach
          @endif
        </select>
      </div>

      {{-- Kecamatan --}}
      <div class="mb-4">
        <label class="block mb-1 font-medium">Kecamatan Tujuan</label>
        <select id="district" name="district_id" class="w-full border rounded p-2" required disabled>
          <option value="">-- Pilih Kecamatan --</option>
        </select>
      </div>

      {{-- Kurir --}}
      <div class="mb-4">
        <label class="block mb-1 font-medium">Kurir</label>
        <select id="courier" name="kurir" class="w-full border rounded p-2" required>
          <option value="">-- Pilih Kurir --</option>
          @foreach($couriers as $courier)
            <option value="{{ strtolower($courier['code']) }}">{{ $courier['name'] }}</option>
          @endforeach
        </select>
      </div>

      {{-- Berat (otomatis, hidden) --}}
      <input type="hidden" id="weight" name="weight" value="{{ $totalWeight }}">
      {{-- <p class="mb-4 text-sm text-gray-600">
        Berat total pesanan: <strong>{{ number_format($totalWeight, 0, ',', '.') }} Gram</strong>
      </p> --}}

      {{-- Layanan Kurir --}}
      <div class="mb-4">
        <label class="block mb-1 font-medium">Layanan Pengiriman</label>
        <select id="service" name="service" class="w-full border rounded p-2" required disabled>
          <option value="">-- Pilih kurir dan kecamatan terlebih dahulu --</option>
        </select>
      </div>

      {{-- Ongkir --}}
      <div class="mb-4">
        <label class="block mb-1 font-medium">Ongkos Kirim</label>
        <input type="text" id="ongkir_display" class="w-full border rounded p-2 bg-gray-100" readonly value="Pilih layanan pengiriman">
        <input type="hidden" name="ongkir" id="ongkir">
      </div>

      {{-- Total --}}
      <div class="mb-6">
        <label class="block mb-1 font-medium">Total Pembayaran</label>
        <input type="text" id="total_bayar_display" class="w-full border rounded p-2 bg-gray-100" readonly
               value="Rp {{ number_format($productSubtotal, 0, ',', '.') }}">
        <input type="hidden" id="total_bayar" name="total_bayar" value="{{ $productSubtotal }}">
      </div>

      <button type="submit" class="bg-green-500 text-white px-6 py-2 rounded hover:bg-green-600">
        Bayar Sekarang
      </button>
    </form>

    {{-- ===================== STRUK PEMBAYARAN ===================== --}}
    <div id="receipt-panel" class="mt-10 hidden print:bg-white">
      <div id="receipt-badge" class="inline-block px-3 py-1 rounded text-white text-sm font-semibold bg-green-600">SUCCESS</div>

      <div class="mt-3 border rounded bg-gray-50">
        <!-- Header Struk -->
        <div class="px-4 py-3 border-b">
          <div class="flex items-center justify-between">
            <h3 class="font-semibold">Struk Pembayaran</h3>
            <span class="text-xs text-gray-500" id="rcp-date">{{ now()->format('d M Y, H:i') }}</span>
          </div>
          <p class="text-xs text-gray-500">Terima kasih sudah berbelanja.</p>
        </div>

        <!-- Ringkasan -->
        <div class="px-4 py-4 grid grid-cols-1 md:grid-cols-2 gap-2 text-sm font-[ui-monospace]">
          <div><span class="text-gray-500">Order ID</span> : <span id="rcp-order">-</span></div>
          <div><span class="text-gray-500">Transaction ID</span> : <span id="rcp-trans">-</span></div>
          <div><span class="text-gray-500">Payment Type</span> : <span id="rcp-type">-</span></div>
          <div><span class="text-gray-500">Status</span> : <span id="rcp-status" class="capitalize">-</span></div>
        </div>

        <!-- Daftar Item -->
        <div class="px-4 pb-4">
          <div class="border-t border-dashed my-2"></div>
          <div class="text-sm">
            <div class="font-semibold mb-2">Item</div>
            <div class="space-y-1">
              @foreach($pesanan->detailPesanans as $d)
                <div class="flex justify-between">
                  <span>{{ $d->produk->nama_produk ?? 'Produk' }} × {{ $d->jumlah }}</span>
                  <span>Rp {{ number_format($d->jumlah * ($d->produk->harga ?? 0), 0, ',', '.') }}</span>
                </div>
              @endforeach
              <div class="flex justify-between" id="rcp-ongkir-row" style="display:none;">
                <span id="rcp-ongkir-label">Ongkos Kirim</span>
                <span id="rcp-ongkir-amount">Rp 0</span>
              </div>
            </div>

            <div class="border-t border-dashed my-2"></div>
            <div class="flex justify-between text-sm">
              <span>Subtotal Produk</span>
              <span id="rcp-subtotal">Rp {{ number_format($productSubtotal, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between text-base font-semibold">
              <span>Total</span>
              <span id="rcp-total">Rp {{ number_format($productSubtotal, 0, ',', '.') }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Aksi -->
      <div class="mt-4 flex flex-wrap gap-2">
        <button onclick="window.print()" class="px-4 py-2 bg-gray-800 text-white rounded hover:bg-black">Cetak Struk</button>
        <a href="{{ route('katalog') }}" class="px-4 py-2 rounded bg-gray-200 hover:bg-gray-300">Kembali Belanja</a>
        <a href="{{ route('home') }}" class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700">Home</a>
      </div>

      <pre id="rcp-raw" class="mt-3 text-xs overflow-auto max-h-64 bg-white p-3 border rounded hidden"></pre>
    </div>
    {{-- =================== /STRUK PEMBAYARAN =================== --}}

  @else
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
      Pesanan tidak ditemukan atau Anda tidak memiliki akses ke pesanan ini.
    </div>
  @endif
</div>

{{-- jQuery --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

{{-- Midtrans Snap (SANDBOX) --}}
<script src="https://app.sandbox.midtrans.com/snap/snap.js"
        data-client-key="{{ config('midtrans.client_key') }}"></script>

<script>
$(function(){
  $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } });

  const $province = $('#province');
  const $city     = $('#city');
  const $district = $('#district');
  const $courier  = $('#courier');
  const $weight   = $('#weight');   // hidden, tetap dipakai JS
  const $service  = $('#service');

  const productTotal = {{ $productSubtotal }};

  function formatRupiah(n){ return 'Rp ' + Number(n||0).toLocaleString('id-ID'); }

  function resetService(placeholder){
    $service.html(`<option>${placeholder}</option>`).prop('disabled', true);
    $('#ongkir_display').val('Pilih layanan pengiriman');
    $('#ongkir').val('');
    $('#total_bayar_display').val(formatRupiah(productTotal));
    $('#total_bayar').val(productTotal);
  }

  function loadCities(provinceId){
    $city.html('<option>Loading...</option>');
    $district.prop('disabled', true).html('<option>-- Pilih Kecamatan --</option>');
    resetService('Pilih kurir terlebih dahulu');
    if (!provinceId) { $city.html('<option value="">-- Pilih Kota --</option>'); return; }
    $.get(`{{ url('/cities') }}/${provinceId}`, function(r){
      $city.empty().append('<option value="">-- Pilih Kota --</option>');
      if(r.success && r.cities.length){
        r.cities.forEach(ct => $city.append(`<option value="${ct.id}">${ct.name}</option>`));
      }
    }).fail(function(){ alert('Gagal memuat kota'); });
  }

  function loadDistricts(cityId){
    $district.prop('disabled', true).html('<option>Loading...</option>');
    resetService('Pilih kurir terlebih dahulu');
    if (!cityId) { $district.html('<option value="">-- Pilih Kecamatan --</option>'); return; }
    $.get(`{{ url('/districts') }}/${cityId}`, function(r){
      $district.empty().append('<option value="">-- Pilih Kecamatan --</option>');
      if(r.success && r.districts.length){
        r.districts.forEach(d => $district.append(`<option value="${d.id}">${d.name}</option>`));
        $district.prop('disabled', false);
      } else {
        alert('Data kecamatan tidak ditemukan.');
      }
    }).fail(function(){ alert('Gagal memuat kecamatan'); });
  }

  function loadServices(){
    const dest    = $district.val();
    const courier = ($courier.val() || '').toLowerCase();
    const weight  = parseInt($weight.val(), 10); // nilai dari hidden input
    if (!dest || !courier || isNaN(weight) || weight < 1) {
      resetService('Isi berat & pilih kurir/kecamatan');
      return;
    }
    $service.html('<option>Memuat layanan...</option>').prop('disabled', true);
    $.post('{{ route("rajaongkir.cost") }}', { destination: dest, courier: courier, weight: weight }, function(resp){
      if (resp && resp.meta && resp.meta.status === 'success' && Array.isArray(resp.data) && resp.data.length) {
        $service.empty().append('<option value="">-- Pilih Layanan --</option>');
        resp.data.forEach((svc) => {
          $service.append(`
            <option value="${svc.service}"
                    data-cost="${svc.cost}"
                    data-etd="${svc.etd}"
                    data-name="${svc.description}"
                    data-code="${svc.service}"
                    data-courier="${svc.courier}">
              ${svc.courier} • ${svc.service} — ${svc.description}
              (Rp ${parseInt(svc.cost).toLocaleString('id-ID')}, ETA ${svc.etd || '-'} hari)
            </option>
          `);
        });
        $service.prop('disabled', false);
      } else {
        resetService('Layanan tidak tersedia');
      }
    }, 'json').fail(function(){ resetService('Layanan tidak tersedia'); });
  }

  $province.on('change', function(){ loadCities($(this).val()); });
  $city.on('change',      function(){ loadDistricts($(this).val()); });
  $district.on('change',  loadServices);
  $courier.on('change',   loadServices);
  let t=null; $weight.on('input blur', function(){ clearTimeout(t); t=setTimeout(loadServices, 300); });

  $service.on('change', function(){
    const opt  = this.options[this.selectedIndex];
    const cost = parseInt(opt?.dataset?.cost || '0', 10);
    $('#ongkir').val(isNaN(cost) ? '' : cost);
    $('#ongkir_display').val(isNaN(cost) ? 'Pilih layanan pengiriman' : formatRupiah(cost));
    const total = productTotal + (isNaN(cost) ? 0 : cost);
    $('#total_bayar_display').val(formatRupiah(total));
    $('#total_bayar').val(total);
  });

  function sendToServer(result){
    fetch("{{ route('payment.confirm') }}", {
      method: "POST",
      headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" },
      body: JSON.stringify({ order_id: result.order_id })
    }).catch(()=>{});
  }

  function showReceipt(status, result){
    const panel = $('#receipt-panel');
    const badge = $('#receipt-badge');
    let color = 'bg-gray-600';
    if(status === 'success') color = 'bg-green-600';
    if(status === 'pending') color = 'bg-yellow-600';
    if(status === 'error')   color = 'bg-red-600';
    badge.removeClass('bg-gray-600 bg-green-600 bg-yellow-600 bg-red-600').addClass(color).text(status.toUpperCase());

    $('#rcp-order').text(result?.order_id || '-');
    $('#rcp-trans').text(result?.transaction_id || '-');
    $('#rcp-type').text(result?.payment_type || '-');
    $('#rcp-status').text(result?.transaction_status || '-');

    const srv = document.getElementById('service');
    const opt = srv && srv.options[srv.selectedIndex];
    const cost = parseInt(opt?.dataset?.cost || '0', 10);
    const courier = opt?.dataset?.courier || '';
    const code    = opt?.dataset?.code || '';
    const name    = opt?.dataset?.name || '';
    const etd     = opt?.dataset?.etd || '';
    if(!isNaN(cost) && cost > 0){
      $('#rcp-ongkir-label').text(`Ongkos Kirim (${courier} ${code} – ${name}${etd ? ', ' + etd + ' hari' : ''})`);
      $('#rcp-ongkir-amount').text(formatRupiah(cost));
      $('#rcp-ongkir-row').show();
    } else {
      $('#rcp-ongkir-row').hide();
    }

    const subtotalProduk = {{ $productSubtotal }};
    const total = subtotalProduk + (isNaN(cost) ? 0 : cost);
    $('#rcp-subtotal').text(formatRupiah(subtotalProduk));
    $('#rcp-total').text(formatRupiah(total));

    $('#rcp-raw').text(JSON.stringify(result || {}, null, 2));
    panel.removeClass('hidden');

    if(status === 'success'){
      const btn = document.querySelector('#checkoutForm button[type="submit"]');
      if(btn){ btn.disabled = true; btn.textContent = 'Pembayaran Berhasil'; }
    }
  }

  $('#checkoutForm').on('submit', function(e){
    e.preventDefault();
    const serviceVal = $('#service').val();
    const ongkirVal  = parseInt($('#ongkir').val(), 10);
    const totalVal   = parseInt($('#total_bayar').val(), 10);
    if (!serviceVal) { alert('Pilih layanan pengiriman dahulu.'); return; }
    if (isNaN(ongkirVal) || ongkirVal < 0) { alert('Ongkir belum terisi.'); return; }
    if (isNaN(totalVal) || totalVal < 1) { alert('Total bayar belum valid.'); return; }

    const $btn = $(this).find('button[type="submit"]');
    $btn.prop('disabled', true).text('Memproses...');

    $.ajax({
      url: $(this).attr('action'),
      method: 'POST',
      data: $(this).serialize(),
      dataType: 'json',
      headers: { Accept: 'application/json' }
    }).done(function(res){
      if (!res || !res.success || !res.snap_token) {
        alert(res && res.message ? res.message : 'Gagal menyiapkan pembayaran.');
        $btn.prop('disabled', false).text('Bayar Sekarang');
        return;
      }
      window.snap.pay(res.snap_token, {
        onSuccess: function(result){ sendToServer(result); showReceipt('success', result); },
        onPending: function(result){ sendToServer(result); showReceipt('pending', result); $btn.prop('disabled', false).text('Bayar Sekarang'); },
        onError:   function(result){ showReceipt('error', result || {}); $btn.prop('disabled', false).text('Bayar Sekarang'); },
        onClose:   function(){ $btn.prop('disabled', false).text('Bayar Sekarang'); }
      });
    }).fail(function(xhr, textStatus){
      let msg = 'Terjadi kesalahan saat menyiapkan pembayaran.';
      if (textStatus === 'parsererror') {
        msg += ' Respons bukan JSON valid (kemungkinan HTML/redirect).';
      } else if (xhr.responseJSON && xhr.responseJSON.message) {
        msg = xhr.responseJSON.message;
      } else if (xhr.status === 419) {
        msg = 'Sesi kedaluwarsa (CSRF). Refresh halaman lalu coba lagi.';
      } else if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
        const firstErr = Object.values(xhr.responseJSON.errors)[0][0];
        msg = 'Validasi gagal: ' + firstErr;
      } else {
        msg += ' [HTTP ' + xhr.status + ']';
      }
      alert(msg);
      $btn.prop('disabled', false).text('Bayar Sekarang');
    });
  });

  resetService('Pilih kurir terlebih dahulu');
});
</script>

<style>
@media print{
  body { background: #fff; }
  nav, header, footer, .no-print, #checkoutForm, #toggle-raw { display:none !important; }
  #receipt-panel { box-shadow:none !important; }
  .border-dashed { border-style: dashed; }
}
</style>
@endsection
