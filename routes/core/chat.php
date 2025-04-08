<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Core\ChatController;

Route::middleware(['auth', 'verified'])->group(function () {
    // Ruta para mostrar el índice de Text-to-Speech
    Route::get('chat', [ChatController::class, 'index'])->name('texttospeech.index');
});
