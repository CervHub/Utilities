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
Route::get('text-to-speech/audio-to-audio', [TextToSpeechApi::class, 'audioToAudio'])->name('texttospeech.audioToAudio');

// Recibe un audio y retorna un texto
Route::get('text-to-speech/audio-to-text', [TextToSpeechApi::class, 'audioToText'])->name('texttospeech.audioToText');

// Ingresa un texto y retorna un audio
Route::get('text-to-speech/text-to-audio', [TextToSpeechApi::class, 'textToAudio'])->name('texttospeech.textToAudio');

// Ingresa un texto y retorna un texto
Route::get('chat/text-to-text', [ChatController::class, 'textToText'])->name('chat.textToText');


// Ruta de prueba que responde con un mensaje de bienvenida
Route::get('test/welcome', function () {
    return response()->json(['message' => 'Bienvenido a la API']);
})->name('test.welcome');

// Ruta de prueba que responde con un mensaje de prueba
Route::get('test/check', function () {
    return response()->json(['message' => 'Prueba exitosa']);
})->name('test.check');
