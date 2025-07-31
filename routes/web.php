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

// Route::prefix('admin')->name('admin.')->group(function () {
//     Route::get('/pengguna', [UserController::class, 'index'])->name('pengguna');
//     Route::get('/pengguna/{id}/edit', [UserController::class, 'edit'])->name('pengguna.edit');
//     Route::delete('/pengguna/{id}', [UserController::class, 'destroy'])->name('pengguna.destroy');
// });
// Route::get('/admin/pengguna', function () {
//     return view('admin.pengguna');
// })->name('admin.pengguna');

Route::middleware(['auth'])->group(function () {
    Route::get('/admin/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');
});


Route::get('/produk/create', [ProdukController::class, 'create'])->name('produk.create');
Route::post('/produk/store', [ProdukController::class, 'store'])->name('admin.produk.store');
Route::get('/admin/produk', [ProdukController::class, 'index'])->name('admin.produk');
Route::post('/admin/formproduk', [ProdukController::class, 'store'])->name('admin.formproduk.store');
Route::get('/admin/formproduk', [ProdukController::class, 'create'])->name('admin.formproduk');
Route::get('/admin/produk/edit/{id}', [ProdukController::class, 'edit'])->name('admin.editproduk');
Route::put('/admin/update-produk/{id}', [ProdukController::class, 'update'])->name('admin.updateproduk');
Route::delete('/admin/produk/delete/{id}', [ProdukController::class, 'destroy'])->name('admin.deleteproduk');




Route::get('admin/kategori', [KategoriController::class, 'index'])->name('admin.kategori');
Route::get('admin/kategori/create', [KategoriController::class, 'create'])->name('admin.formkategori');
Route::post('admin/kategori', [KategoriController::class, 'store'])->name('admin.storekategori');
Route::get('/admin/kategori/edit/{id}', [KategoriController::class, 'edit'])->name('admin.editkategori');
Route::put('admin/kategori/{id}', [KategoriController::class, 'update'])->name('admin.updatekategori');
Route::delete('admin/kategori/{id}', [KategoriController::class, 'destroy'])->name('admin.deletekategori');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';


// Route::get('/users', [UserController::class, 'index']);
// Route::post('/users', [UserController::class, 'store']);
// Route::get('/users/{id}', [UserController::class, 'show']);
// Route::put('/users/{id}', [UserController::class, 'update']);
// Route::delete('/users/{id}', [UserController::class, 'destroy']);



// Route::apiResource('stok', StokController::class);





// Route::apiResource('detail-pesanan', DetailPesananController::class);



// Route::apiResource('ulasan-produk', UlasanProdukController::class);



// Route::apiResource('pembayaran', PembayaranController::class);


