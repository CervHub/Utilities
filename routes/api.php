<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TextToSpeechApi;
use App\Http\Controllers\Api\ChatController;
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Rutas para las APIs de procesamiento de audio y texto
// Recibe un audio y retorna otro audio
Route::post('text-to-speech/audio-to-audio', [TextToSpeechApi::class, 'audioToAudio'])->name('texttospeech.audioToAudio');

// Recibe un audio y retorna un texto
Route::post('text-to-speech/audio-to-text', [TextToSpeechApi::class, 'audioToText'])->name('texttospeech.audioToText');

// Ingresa un texto y retorna un audio
Route::post('text-to-speech/text-to-audio', [TextToSpeechApi::class, 'textToAudio'])->name('texttospeech.textToAudio');

// Ingresa un texto y retorna un texto
Route::post('chat/text-to-text', [ChatController::class, 'textToText'])->name('chat.textToText');
