<?php

// File: routes/api.php

use Illuminate\Http\Request;
use App\Http\Controllers\Api\UserProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\IkanController;
use App\Http\Controllers\Api\PesananApiController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\KeranjangController;
use App\Http\Controllers\Api\PaymentProofController; // <-- TAMBAHKAN IMPORT INI

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| This is where you can register API routes for your application.
| These routes are loaded by the RouteServiceProvider and all of them
| will be assigned to the "api" middleware group. Make something great!
|
*/

// == API Endpoints Katalog Ikan (Publik) ==

// Endpoint untuk mendapatkan daftar kategori ikan
Route::get('/kategori', [IkanController::class, 'daftarKategori'])->name('api.kategori.index');

// Endpoint untuk mendapatkan daftar ikan
Route::get('/ikan', [IkanController::class, 'index'])->name('api.ikan.index');

// Endpoint untuk mendapatkan informasi ikan berdasarkan slug
Route::get('/ikan/{ikan:slug}', [IkanController::class, 'show'])->name('api.ikan.show');


// == API Endpoints Otentikasi (Publik) ==

// Endpoint untuk melakukan registrasi pengguna
Route::post('/register', [AuthController::class, 'register'])->name('api.register');

// Endpoint untuk login dan mendapatkan token autentikasi
Route::post('/login', [AuthController::class, 'login'])->name('api.login');


// == API Endpoints yang Memerlukan Otentikasi (Sanctum Token) ==

// Semua endpoint yang memerlukan otentikasi berada di dalam grup ini
Route::middleware('auth:sanctum')->group(function () {

    // Endpoint untuk melakukan logout dan menghapus token
    Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');

    // Endpoint untuk mendapatkan data pengguna yang sedang login
    Route::get('/user', [AuthController::class, 'user'])->name('api.user');

    // Endpoint untuk membuat pesanan baru
    // Route::get('/pesanan', [PesananApiController::class, 'store'])->name('api.pesanan.store');
    Route::get('/pesanan', [PesananApiController::class, 'index'])->name('api.pesanan.index');
    Route::get('/pesanan/{pesanan}', [PesananApiController::class, 'show'])->name('api.pesanan.show');
    Route::post('/pesanan', [PesananApiController::class, 'store'])->name('api.pesanan.store');
    Route::put('/pesanan/{pesanan}', [PesananApiController::class, 'update'])->name('api.pesanan.update'); // Atau PATCH
    Route::delete('/pesanan/{pesanan}', [PesananApiController::class, 'destroy'])->name('api.pesanan.destroy');
    // === TAMBAHAN ROUTE UNTUK SUBMIT BUKTI PEMBAYARAN ===
    // Parameter {pesanan} akan di-resolve menjadi instance model Pesanan (Route Model Binding)
    Route::post('/pesanan/{pesanan}/submit-payment-proof', [PaymentProofController::class, 'submitProof'])
        ->name('api.pesanan.submitProof');
    // =====================================================
    Route::put('/pesanan/{pesanan}/tandai-selesai', [PesananApiController::class, 'tandaiSelesai'])->name('api.pesanan.tandaiSelesai');
    //untuk mengelola pesanan:
    // Route::get('/pesanan', [PesananApiController::class, 'index'])->name('api.pesanan.index');
    // Route::get('/pesanan/{pesanan}', [PesananApiController::class, 'show'])->name('api.pesanan.show');
    // Route::put('/pesanan/{pesanan}', [PesananApiController::class, 'update'])->name('api.pesanan.update');
    // Route::delete('/pesanan/{pesanan}', [PesananApiController::class, 'destroy'])->name('api.pesanan.destroy');

    // Letakkan endpoint API lain yang memerlukan user login di dalam grup ini
    Route::get('/keranjang', [KeranjangController::class, 'index'])->name('keranjang.index');
    Route::post('/keranjang', [KeranjangController::class, 'store'])->name('keranjang.store');
    // Nama parameter {keranjangItem} harus cocok dengan variabel $keranjangItem di controller
    Route::put('/keranjang/{keranjangItem}', [KeranjangController::class, 'update'])->name('keranjang.update');
    Route::delete('/keranjang/{keranjangItem}', [KeranjangController::class, 'destroy'])->name('keranjang.destroy');

    // Endpoint untuk update foto profil pengguna
    Route::post('/user/profile-photo', [UserProfileController::class, 'updateProfilePhoto'])->name('user.photo.update');
    // Tambahkan route untuk menghapus foto jika perlu
    // Route::delete('/user/profile-photo', [UserProfileController::class, 'deleteProfilePhoto'])->name('user.photo.delete');
});

// Route fallback jika endpoint API tidak ditemukan (opsional)
// Jika endpoint yang diminta tidak ada, akan memberikan respons error 404
Route::fallback(function () {
    return response()->json(['message' => 'Endpoint tidak ditemukan.'], 404);
});

// Catatan: Anda memiliki dua blok Route::middleware('auth:sanctum')->group(...).
// Biasanya, cukup satu grup utama untuk semua route terotentikasi API.
// Namun, saya telah menambahkan route baru ke grup pertama yang lebih besar sesuai struktur Anda.
// Jika /user/profile-photo juga merupakan bagian dari API utama, Anda bisa memindahkannya ke grup pertama.