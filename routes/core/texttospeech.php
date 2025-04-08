<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Core\TextToSpeechController;

Route::middleware(['auth', 'verified'])->group(function () {
    // Ruta para mostrar el índice de Text-to-Speech
    Route::get('text-to-speech', [TextToSpeechController::class, 'index'])->name('texttospeech.index');
});
