<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MapController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Tambahkan route berikut ke dalam file routes/web.php Laravel Anda.
|
*/

Route::get('/map', [MapController::class, 'index'])->name('map');

// Jika ingin halaman utama langsung ke peta:
// Route::get('/', [MapController::class, 'index']);