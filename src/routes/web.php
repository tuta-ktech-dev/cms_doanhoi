<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\QRCodeController;

Route::get('/', function () {
    return view('welcome');
});

// QR Code image route (public, but token-based security)
Route::get('/qr-code/{token}', [QRCodeController::class, 'serveImage'])
    ->name('qr-code.image');
