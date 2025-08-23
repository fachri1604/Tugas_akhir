<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\StokController;
use App\Http\Controllers\DetailPesananController;
use App\Http\Controllers\UlasanProdukController;
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PesananController;
use App\Http\Controllers\RajaOngkirController;
use App\Http\Controllers\DashboardController;
use App\Models\Produk; 
use Illuminate\Http\Request;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as FrameworkCsrf;  

// ==================================================
// Helper sederhana
// ==================================================
$redirectIfAdmin = function () {
    if (Auth::check() && strcasecmp(Auth::user()->role, 'admin') === 0) {
        return redirect()->route('admin.dashboard');
    }
    return null;
};

$ensureAdmin = function () {
    if (!Auth::check()) {
        return redirect()->route('login');
    }
    if (strcasecmp(Auth::user()->role, 'admin') !== 0) {
        abort(403, 'Forbidden');
    }
    return null;
};

// ==================================================
// Halaman publik (admin dilarang → redirect dashboard)
// ==================================================
Route::get('/', function () use ($redirectIfAdmin) {
    if ($resp = $redirectIfAdmin()) return $resp;

    $products = Produk::orderBy('id_produk', 'desc')
        ->take(6)
        ->get(['id_produk','nama_produk','gambar_produk']);
    return view('home', compact('products'));
})->name('home');

Route::get('/produk2', function () use ($redirectIfAdmin) {
    if ($resp = $redirectIfAdmin()) return $resp;
    return view('produk2');
})->name('produk2.index');

Route::get('/login', function () use ($redirectIfAdmin) {
    if ($resp = $redirectIfAdmin()) return $resp;
    return app(LoginController::class)->showLoginForm();
})->name('login');
Route::post('/login', [LoginController::class, 'login']);

Route::get('/register', function () use ($redirectIfAdmin) {
    if ($resp = $redirectIfAdmin()) return $resp;
    return app(RegisteredUserController::class)->create();
})->name('register');
Route::post('/register', [RegisteredUserController::class, 'store']);

Route::get('/katalog', function () use ($redirectIfAdmin) {
    if ($resp = $redirectIfAdmin()) return $resp;
    return app(App\Http\Controllers\ProdukController::class)->katalog(request());
})->name('katalog');

Route::get('/produk/beli/{id}', function ($id) use ($redirectIfAdmin) {
    if ($resp = $redirectIfAdmin()) return $resp;
    return app(ProdukController::class)->beli($id);
})->name('produk.beli');

Route::post('/beli/{id}', [ProdukController::class, 'beli'])->name('beli.produk');
Route::get('/produk/{id}', [ProdukController::class, 'detail'])
     ->name('produk.detail');
// ==================================================
// User login biasa
// ==================================================
Route::middleware(['auth'])->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add/{id_produk}', [CartController::class, 'add'])->name('cart.add');
    Route::put('/cart/update/{id_detail}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/remove/{id_detail}', [CartController::class, 'remove'])->name('cart.remove');
    Route::get('/checkout', [CartController::class, 'checkout'])->name('cart.checkout');

    Route::get('/checkout/{id_pesanan}', [CartController::class, 'checkoutForm'])->name('checkout.form');
    Route::post('/checkout/{id_pesanan}', [CartController::class, 'checkoutProcess'])->name('checkout.process');

    // Route::get('/payment/{order_id}', [PaymentController::class, 'show'])->name('payment.show');

    Route::get('/riwayat-pesanan', [PesananController::class, 'riwayat'])
    ->name('riwayat.index');

// Detail riwayat; binding berdasarkan kolom id_pesanan (BUKAN id)
Route::get('/riwayat-pesanan/{pesanan:id_pesanan}', [PesananController::class, 'riwayatShow'])
    ->name('riwayat.show');

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->middleware(['verified'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
});
Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

// ==================================================
// Admin-only routes (pakai $ensureAdmin)
// ==================================================
Route::get('/admin/dashboard', function () use ($ensureAdmin) {
    if ($resp = $ensureAdmin()) return $resp;

    // Buat instance controller + injeksikan Request
    $controller = app(DashboardController::class);
    return $controller->index(request());
})->name('admin.dashboard');

// Pengguna
// LIST + (opsional) edit_id dari query
Route::get('/admin/pengguna', function (Request $request) use ($ensureAdmin) {
    if ($resp = $ensureAdmin()) return $resp;
    // panggil sebagai instance + inject Request
    return app(UserController::class)->index($request);
})->name('admin.pengguna');

Route::patch('/admin/pengguna/{id_user}', function (Request $request, $id_user) use ($ensureAdmin) {
    if ($resp = $ensureAdmin()) return $resp;
    return app(UserController::class)->update($request, $id_user);
})->name('admin.pengguna.update');

Route::delete('/admin/pengguna/{id_user}', function ($id_user) use ($ensureAdmin) {
    if ($resp = $ensureAdmin()) return $resp;
    return app(UserController::class)->destroy($id_user);
})->name('admin.pengguna.destroy');

// Produk
Route::get('/admin/produk', function () use ($ensureAdmin) {
    if ($resp = $ensureAdmin()) return $resp;
    return app(ProdukController::class)->index();
})->name('admin.produk');
Route::get('/admin/produk/edit/{id}', function ($id) use ($ensureAdmin) {
    if ($resp = $ensureAdmin()) return $resp;
    return app(ProdukController::class)->edit($id);
})->name('admin.editproduk');
Route::put('/admin/update-produk/{id}', function ($id) use ($ensureAdmin) {
    if ($resp = $ensureAdmin()) return $resp;
    return app(ProdukController::class)->update(request(), $id);
})->name('admin.updateproduk');
Route::delete('/admin/produk/delete/{id}', function ($id) use ($ensureAdmin) {
    if ($resp = $ensureAdmin()) return $resp;
    return app(ProdukController::class)->destroy($id);
})->name('admin.deleteproduk');
Route::get('/admin/formproduk', function () use ($ensureAdmin) {
    if ($resp = $ensureAdmin()) return $resp;
    return app(ProdukController::class)->create();
})->name('admin.formproduk');
Route::post('/admin/formproduk', function () use ($ensureAdmin) {
    if ($resp = $ensureAdmin()) return $resp;
    return app(ProdukController::class)->store(request());
})->name('admin.produk.store');

// Kategori
Route::get('/admin/kategori', function () use ($ensureAdmin) {
    if ($resp = $ensureAdmin()) return $resp;
    return app(KategoriController::class)->index();
})->name('admin.kategori');
Route::get('/admin/kategori/create', function () use ($ensureAdmin) {
    if ($resp = $ensureAdmin()) return $resp;
    return app(KategoriController::class)->create();
})->name('admin.formkategori');
Route::post('/admin/kategori', function () use ($ensureAdmin) {
    if ($resp = $ensureAdmin()) return $resp;
    return app(KategoriController::class)->store(request());
})->name('admin.storekategori');
Route::get('/admin/kategori/edit/{id}', function ($id) use ($ensureAdmin) {
    if ($resp = $ensureAdmin()) return $resp;
    return app(KategoriController::class)->edit($id);
})->name('admin.editkategori');
Route::put('/admin/kategori/{id}', function ($id) use ($ensureAdmin) {
    if ($resp = $ensureAdmin()) return $resp;
    return app(KategoriController::class)->update(request(), $id);
})->name('admin.updatekategori');
Route::delete('/admin/kategori/{id}', function ($id) use ($ensureAdmin) {
    if ($resp = $ensureAdmin()) return $resp;
    return app(KategoriController::class)->destroy($id);
})->name('admin.deletekategori');

// Pesanan
Route::get('/admin/pesanan', function () use ($ensureAdmin) {
    if ($resp = $ensureAdmin()) return $resp;
    return app(PesananController::class)->index();
})->name('admin.pesanan');

// ==================================================
// RajaOngkir & Midtrans (tetap sama)
// ==================================================
Route::get('/provinces', [RajaOngkirController::class, 'getProvinces'])->name('rajaongkir.provinces');
Route::get('/cities/{province_id}', [RajaOngkirController::class, 'getCities'])->name('rajaongkir.cities');
Route::get('/districts/{city_id}', [RajaOngkirController::class, 'getDistricts'])->name('rajaongkir.districts');
Route::prefix('ongkir')->name('rajaongkir.')->group(function () {
    Route::post('/cost', [RajaOngkirController::class, 'getCost'])->name('cost');
});

Route::post('/payment/notification', [PaymentController::class, 'notificationHandler'])
    ->name('payment.notification')
    ->withoutMiddleware([FrameworkCsrf::class]);
Route::get('/payment/success/{order_id}', [PaymentController::class, 'paymentSuccess'])->name('payment.success');
Route::get('/payment/unfinish/{order_id}', [PaymentController::class, 'paymentUnfinish'])->name('payment.unfinish');
Route::get('/payment/error/{order_id}', [PaymentController::class, 'paymentError'])->name('payment.error');
Route::post('/payment/confirm', [PaymentController::class, 'confirmFromClient'])->name('payment.confirm');

require __DIR__.'/auth.php';
