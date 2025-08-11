<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
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



Route::get('/produk2', function () {
    return view('produk2');
})->name('produk2.index');



Route::get('/admin/pengguna', [UserController::class, 'index'])->name('admin.pengguna');
Route::patch('/admin/pengguna/{id}', [UserController::class, 'update'])->name('admin.pengguna.update');
Route::delete('/admin/pengguna/{id}', [UserController::class, 'destroy'])->name('admin.pengguna.destroy');

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
Route::post('/register', [RegisteredUserController::class, 'store']);


Route::get('/', function () {
    return view('home'); // Pastikan file home.blade.php ada di resources/views/
})->name('home');




Route::middleware(['auth'])->group(function () {
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add/{id_produk}', [CartController::class, 'add'])->name('cart.add');
    Route::put('/cart/update/{id_detail}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/remove/{id_detail}', [CartController::class, 'remove'])->name('cart.remove');
    Route::get('/checkout', [CartController::class, 'checkout'])->name('cart.checkout');

    // Payment show by order_id
    Route::get('/payment/{order_id}', [PaymentController::class, 'show'])->name('payment.show');
});

// Midtrans Notification
Route::post('/payment/notification', [PaymentController::class, 'notificationHandler'])->name('payment.notification');

// Midtrans Redirect
Route::get('/payment/success/{order_id}', [PaymentController::class, 'paymentSuccess'])->name('payment.success');
Route::get('/payment/unfinish/{order_id}', [PaymentController::class, 'paymentUnfinish'])->name('payment.unfinish');
Route::get('/payment/error/{order_id}', [PaymentController::class, 'paymentError'])->name('payment.error');






Route::middleware(['auth'])->group(function () {
    Route::get('/admin/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');
});


Route::get('/produk/create', [ProdukController::class, 'create'])->name('produk.create');
Route::post('/produk/store', [ProdukController::class, 'store'])->name('admin.produk.store');
Route::get('/produk/detail/{id}', [ProdukController::class, 'getDetail'])->name('produk.detail');
Route::get('/admin/produk', [ProdukController::class, 'index'])->name('admin.produk');
Route::post('/admin/formproduk', [ProdukController::class, 'store'])->name('admin.formproduk.store');
Route::get('/admin/formproduk', [ProdukController::class, 'create'])->name('admin.formproduk');
Route::get('/admin/produk/edit/{id}', [ProdukController::class, 'edit'])->name('admin.editproduk');
Route::put('/admin/update-produk/{id}', [ProdukController::class, 'update'])->name('admin.updateproduk');
Route::delete('/admin/produk/delete/{id}', [ProdukController::class, 'destroy'])->name('admin.deleteproduk');

Route::get('/katalog', [ProdukController::class, 'katalog'])->name('katalog');
Route::post('/beli/{id}', [ProdukController::class, 'beli'])->name('beli.produk');
Route::get('/produk/beli/{id}', [ProdukController::class, 'beli'])->name('produk.beli');


Route::get('admin/kategori', [KategoriController::class, 'index'])->name('admin.kategori');
Route::get('admin/kategori/create', [KategoriController::class, 'create'])->name('admin.formkategori');
Route::post('admin/kategori', [KategoriController::class, 'store'])->name('admin.storekategori');
Route::get('/admin/kategori/edit/{id}', [KategoriController::class, 'edit'])->name('admin.editkategori');
Route::put('admin/kategori/{id}', [KategoriController::class, 'update'])->name('admin.updatekategori');
Route::delete('admin/kategori/{id}', [KategoriController::class, 'destroy'])->name('admin.deletekategori');

Route::get('admin/pesanan', [PesananController::class, 'index'])->name('admin.pesanan');
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// routes/web.php


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
});

require __DIR__.'/auth.php';


Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');