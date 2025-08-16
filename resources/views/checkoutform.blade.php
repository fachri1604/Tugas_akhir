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

  @if($pesanan)
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

    {{-- Berat --}}
    <div class="mb-4">
      <label class="block mb-1 font-medium">Berat Total (gram)</label>
      <input type="number" id="weight" name="weight" class="w-full border rounded p-2"
             value="{{ $pesanan->detailPesanans->sum(fn($i) => $i->produk->berat * $i->jumlah) }}"
             min="1" required>
    </div>

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
             value="Rp {{ number_format((int) $pesanan->total_harga, 0, ',', '.') }}">
      <input type="hidden" id="total_bayar" name="total_bayar" value="{{ (int) $pesanan->total_harga }}">
    </div>

    <button type="submit" class="bg-green-500 text-white px-6 py-2 rounded hover:bg-green-600">
      Bayar Sekarang
    </button>
  </form>

  {{-- PANEL STATUS TRANSAKSI (Muncul setelah bayar) --}}
  <div id="status-panel" class="mt-8 hidden">
    <div id="status-badge" class="inline-block px-3 py-1 rounded text-white text-sm font-semibold"></div>
    <div class="mt-3 p-4 border rounded bg-gray-50">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
        <div><span class="font-medium">Order ID:</span> <span id="s-order-id">-</span></div>
        <div><span class="font-medium">Transaction ID:</span> <span id="s-trans-id">-</span></div>
        <div><span class="font-medium">Payment Type:</span> <span id="s-type">-</span></div>
        <div><span class="font-medium">Status:</span> <span id="s-status">-</span></div>
        <div class="md:col-span-2"><span class="font-medium">Gross Amount:</span> <span id="s-amount">-</span></div>
      </div>
      <pre id="s-raw" class="mt-3 text-xs overflow-auto max-h-48 bg-white p-3 border rounded"></pre>
    </div>
    <div class="mt-4 flex gap-2">
      <a href="{{ route('katalog') }}" class="px-4 py-2 rounded bg-gray-200 hover:bg-gray-300">Kembali Belanja</a>
      <a href="{{ route('home') }}" class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700">Home</a>
    </div>
  </div>
  @else
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
      Pesanan tidak ditemukan atau Anda tidak memiliki akses ke pesanan ini.
    </div>
  @endif
</div>

{{-- jQuery --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

{{-- Midtrans Snap (SANDBOX). Ganti ke production saat live --}}
<script src="https://app.sandbox.midtrans.com/snap/snap.js"
        data-client-key="{{ config('midtrans.client_key') }}"></script>

<script>
$(function(){
  $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } });

  const $province = $('#province');
  const $city     = $('#city');
  const $district = $('#district');
  const $courier  = $('#courier');
  const $weight   = $('#weight');
  const $service  = $('#service');

  const productTotal = {{ (int) $pesanan->total_harga }};

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
    const weight  = parseInt($weight.val(), 10);

    if (!dest || !courier || isNaN(weight) || weight < 1) {
      resetService('Isi berat & pilih kurir/kecamatan');
      return;
    }

    $service.html('<option>Memuat layanan...</option>').prop('disabled', true);

    $.post('{{ route("rajaongkir.cost") }}', {
      destination: dest,
      courier: courier,
      weight: weight
    }, function(resp){
      if (resp.meta && resp.meta.status === 'success' && resp.data.length) {
        $service.empty().append('<option value="">-- Pilih Layanan --</option>');
        resp.data.forEach((svc) => {
          $service.append(`
            <option
              value="${svc.service}"
              data-cost="${svc.cost}"
              data-etd="${svc.etd}"
              data-name="${svc.description}"
              data-code="${svc.service}">
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

  // Events lokasi/kurir/berat
  $province.on('change', function(){ loadCities($(this).val()); });
  $city.on('change',      function(){ loadDistricts($(this).val()); });
  $district.on('change',  loadServices);
  $courier.on('change',   loadServices);
  let t=null;
  $weight.on('input blur', function(){ clearTimeout(t); t=setTimeout(loadServices, 300); });

  // Saat pilih layanan -> set ongkir & total
  $service.on('change', function(){
    const opt  = this.options[this.selectedIndex];
    const cost = parseInt(opt?.dataset?.cost || '0', 10);
    $('#ongkir').val(isNaN(cost) ? '' : cost);
    $('#ongkir_display').val(isNaN(cost) ? 'Pilih layanan pengiriman' : formatRupiah(cost));
    const total = productTotal + (isNaN(cost) ? 0 : cost);
    $('#total_bayar_display').val(formatRupiah(total));
    $('#total_bayar').val(total);
  });

  // ====== Kirim konfirmasi aman (CSRF) ke server ======
  function sendToServer(result){
    // Hanya kirim order_id; server akan cek status langsung ke Midtrans
    fetch("{{ route('payment.confirm') }}", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": "{{ csrf_token() }}"
      },
      body: JSON.stringify({ order_id: result.order_id })
    }).catch(()=>{});
  }

  // ===== Helper tampilkan status transaksi =====
  function showStatus(status, result){
    const badge = $('#status-badge');
    const panel = $('#status-panel');

    let color = 'bg-gray-500';
    if(status === 'success') color = 'bg-green-600';
    if(status === 'pending') color = 'bg-yellow-600';
    if(status === 'error')   color = 'bg-red-600';

    badge.removeClass('bg-gray-500 bg-green-600 bg-yellow-600 bg-red-600')
         .addClass(color)
         .text(status.toUpperCase());

    $('#s-order-id').text(result?.order_id || '-');
    $('#s-trans-id').text(result?.transaction_id || '-');
    $('#s-type').text(result?.payment_type || '-');
    $('#s-status').text(result?.transaction_status || '-');
    $('#s-amount').text(result?.gross_amount ? formatRupiah(result.gross_amount) : '-');
    $('#s-raw').text(JSON.stringify(result || {}, null, 2));

    panel.removeClass('hidden');

    if(status === 'success'){
      const btn = document.querySelector('#checkoutForm button[type="submit"]');
      if(btn){ btn.disabled = true; btn.textContent = 'Pembayaran Berhasil'; }
    }
  }

  // ===== SUBMIT FORM -> AJAX -> SNAP POPUP =====
  $('#checkoutForm').on('submit', function(e){
    e.preventDefault();

    if (!$service.val()) {
      alert('Pilih layanan pengiriman dahulu.');
      return;
    }

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

      // Panggil Snap popup di halaman ini (TANPA redirect)
      window.snap.pay(res.snap_token, {
        onSuccess: function(result){
          // Trigger UX + minta server konfirmasi status ke Midtrans
          sendToServer(result);
          showStatus('success', result);
        },
        onPending: function(result){
          sendToServer(result);
          showStatus('pending', result);
          $btn.prop('disabled', false).text('Bayar Sekarang');
        },
        onError: function(result){
          showStatus('error', result || {});
          $btn.prop('disabled', false).text('Bayar Sekarang');
        },
        onClose: function(){
          // User menutup popup tanpa menyelesaikan pembayaran
          $btn.prop('disabled', false).text('Bayar Sekarang');
        }
      });

    }).fail(function(xhr){
      let msg = 'Terjadi kesalahan saat menyiapkan pembayaran.';
      if (xhr.responseJSON && xhr.responseJSON.message) {
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

  // init
  resetService('Pilih kurir terlebih dahulu');
});
</script>
@endsection
