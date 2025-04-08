<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\AwsService;

class TextToSpeechApi extends Controller
{
    protected $awsService;

    public function __construct(AwsService $awsService)
    {
        $this->awsService = $awsService;
    }

    public function audioToAudio(Request $request)
    {


        // Obtener el archivo de audio
        $audioFile = $request->file('audio');

        try {
            // Delegar la lógica al servicio AWS
            $response = $this->awsService->processAudioFile($audioFile);

            return response()->json([
                'message' => 'Audio processed successfully',
                'data' => $response,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error processing audio',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function audioToText(Request $request)
    {

        // Obtener el archivo de audio
        $audioFile = $request->file('audio');

        try {
            // Delegar la lógica al servicio AWS
            $response = $this->awsService->convertAudioToText($audioFile);

            return response()->json([
                'message' => 'Audio converted to text successfully',
                'data' => $response,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error converting audio to text',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function textToAudio(Request $request)
    {


        // Obtener el texto
        $text = $request->input('text');

        try {
            // Delegar la lógica al servicio AWS
            $response = $this->awsService->convertTextToAudio($text);

            return response()->json([
                'message' => 'Text converted to audio successfully',
                'data' => $response,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error converting text to audio',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
