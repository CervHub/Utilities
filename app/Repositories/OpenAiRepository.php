<?php

namespace App\Repositories;

use Illuminate\Support\Facades\Http;
use App\Models\Thread;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class OpenAiRepository
{
    private $apiKey;
    private $assistantId;
    private $assistantCervId;
    private $url;

    public function __construct()
    {
        $this->apiKey = env('OPENAI_API_KEY');
        $this->assistantId = env('OPENAI_ASSISTANT_ID'); // Reemplaza con tu Assistant ID
        $this->assistantCervId = env('OPENAI_ASSISTANT_CERV_ID'); // Reemplaza con tu Assistant ID
        $this->url = 'https://api.openai.com/v1/chat/completions';
    }

    public function transcribe($audioFile)
    {
        Log::info("message", ['audioFile' => $audioFile]);

        // Validar que $audioFile sea una instancia de UploadedFile
        if (!$audioFile instanceof \Illuminate\Http\UploadedFile) {
            throw new \InvalidArgumentException('El archivo proporcionado no es válido.');
        }

        try {
            // Enviar el archivo como multipart/form-data
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])->attach(
                'file', // Nombre del campo esperado por la API
                fopen($audioFile->getPathname(), 'r'), // Archivo en binario
                $audioFile->getClientOriginalName() // Nombre original del archivo
            )->post('https://api.openai.com/v1/audio/transcriptions', [
                'model' => 'whisper-1', // Modelo de transcripción
            ]);

            if ($response->successful()) {
                $responseData = $response->json();

                // Retornar el texto transcrito
                if (isset($responseData['text'])) {
                    return $responseData['text'];
                }

                return 'No se pudo obtener la transcripción del audio.';
            } else {
                Log::error("Error en la API: " . $response->status() . " - " . $response->body());
                return $this->handleErrorResponse($response->body());
            }
        } catch (\Exception $e) {
            Log::error("Excepción: " . $e->getMessage());
            return $this->handleErrorResponse($e->getMessage());
        }
    }

    public function chat($message)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->url, [
                'model' => 'gpt-4o-mini-2024-07-18',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'Eres Cervito, desarrollado por la Empresa Cerv de Perú. Responde de forma corta, concisa, precisa y con un máximo de 200 caracteres.',
                    ],
                    [
                        'role' => 'user',
                        'content' => $message,
                    ],
                ],
            ]);

            if ($response->successful()) {
                $responseData = $response->json();

                // Extraer el mensaje limpio de la estructura
                if (isset($responseData['choices'][0]['message']['content'])) {
                    return $responseData['choices'][0]['message']['content'];
                }

                if (isset($responseData['message']['original']['message'])) {
                    return $responseData['message']['original']['message'];
                }

                return 'No se pudo obtener el contenido del mensaje.';
            } else {
                return $this->handleErrorResponse();
            }
        } catch (\Exception $e) {
            return $this->handleErrorResponse($e->getMessage());
        }
    }

    public function checkMessage($message, $instructions)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->url, [
                'model' => 'gpt-4o-mini-2024-07-18',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => $instructions,
                    ],
                    [
                        'role' => 'user',
                        'content' => $message,
                    ],
                ],
            ]);

            if ($response->successful()) {
                return $response->json()['choices'][0]['message']['content'];
            } else {
                return $this->handleErrorResponse();
            }
        } catch (\Exception $e) {
            return $this->handleErrorResponse($e->getMessage());
        }
    }

    public function assistant($message, $threadId)
    {
        try {
            // Agregar el mensaje al thread
            $addMessageResponse = $this->addMessage($threadId, $message);

            if (isset($addMessageResponse['error'])) {
                return $this->handleErrorResponse($addMessageResponse['error']);
            }

            // Ejecutar el thread
            $runThreadResponse = $this->runThread($threadId, $this->assistantId);

            if (isset($runThreadResponse['error'])) {
                return $this->handleErrorResponse($runThreadResponse['error']);
            }

            // Filtrar las respuestas que cumplan con los criterios
            $filteredResponses = array_filter($runThreadResponse, function ($item) {
                return isset($item['object']) && $item['object'] === 'thread.message' && isset($item['status']) && $item['status'] === 'completed';
            });

            // Extraer los valores de item.text.value
            $textValues = array_map(function ($item) {
                if (isset($item['content']) && is_array($item['content'])) {
                    foreach ($item['content'] as $content) {
                        if (isset($content['type']) && $content['type'] === 'text' && isset($content['text']['value'])) {
                            return $content['text']['value'];
                        }
                    }
                }
                return null;
            }, $filteredResponses);

            // Filtrar valores nulos
            $textValues = array_filter($textValues);
            $content = reset($textValues);
            $content = str_replace(['```html', '```'], '', $content); // Eliminar etiquetas ```html y ```
            $runThreadResponse['parsed_content'] = $content;

            // Devolver el primer valor de text
            return $content;
        } catch (\Exception $e) {
            return $this->handleErrorResponse($e->getMessage());
        }
    }

    public function cervChat($message, $threadId)
    {
        try {
            // Agregar el mensaje al thread
            $addMessageResponse = $this->addMessage($threadId, $message);

            if (isset($addMessageResponse['error'])) {
                return $this->handleErrorResponse($addMessageResponse['error']);
            }

            // Ejecutar el thread
            $runThreadResponse = $this->runThread($threadId, $this->assistantCervId);

            if (isset($runThreadResponse['error'])) {
                return $this->handleErrorResponse($runThreadResponse['error']);
            }

            // Filtrar las respuestas que cumplan con los criterios
            $filteredResponses = array_filter($runThreadResponse, function ($item) {
                return isset($item['object']) && $item['object'] === 'thread.message' && isset($item['status']) && $item['status'] === 'completed';
            });

            // Extraer los valores de item.text.value
            $textValues = array_map(function ($item) {
                if (isset($item['content']) && is_array($item['content'])) {
                    foreach ($item['content'] as $content) {
                        if (isset($content['type']) && $content['type'] === 'text' && isset($content['text']['value'])) {
                            return $content['text']['value'];
                        }
                    }
                }
                return null;
            }, $filteredResponses);

            // Filtrar valores nulos
            $textValues = array_filter($textValues);
            $content = reset($textValues);
            $content = str_replace(['```html', '```'], '', $content); // Eliminar etiquetas ```html y ```
            $runThreadResponse['parsed_content'] = $content;

            // Devolver el primer valor de text
            return $content;
        } catch (\Exception $e) {
            return $this->handleErrorResponse($e->getMessage());
        }
    }

    public function listAssistants($order = 'desc', $limit = 20)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
                'OpenAI-Beta' => 'assistants=v2',
            ])->get('https://api.openai.com/v1/assistants', [
                'order' => $order,
                'limit' => $limit,
            ]);

            if ($response->successful()) {
                return $response->json();
            } else {
                return $this->handleErrorResponse();
            }
        } catch (\Exception $e) {
            return $this->handleErrorResponse($e->getMessage());
        }
    }

    public function createThread($apiKey, $userId = null)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
                'OpenAI-Beta' => 'assistants=v2',
            ])->post('https://api.openai.com/v1/threads', []);

            if ($response->successful()) {
                $responseData = $response->json();

                // Crear el modelo Thread y guardarlo en la base de datos
                $thread = Thread::create([
                    'thread_id' => $responseData['id'],
                    'apikey' => $apiKey,
                    'user_id' => $userId,
                ]);

                return $thread;
            } else {
                return $this->handleErrorResponse();
            }
        } catch (\Exception $e) {
            return $this->handleErrorResponse($e->getMessage());
        }
    }

    public function getThread($threadId)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
                'OpenAI-Beta' => 'assistants=v2',
            ])->get("https://api.openai.com/v1/threads/{$threadId}");

            if ($response->successful()) {
                return $response->json();
            } else {
                return $this->handleErrorResponse();
            }
        } catch (\Exception $e) {
            return $this->handleErrorResponse($e->getMessage());
        }
    }

    public function deleteThread($threadId)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
                'OpenAI-Beta' => 'assistants=v2',
            ])->delete("https://api.openai.com/v1/threads/{$threadId}");

            if ($response->successful()) {
                return $response->json();
            } else {
                return $this->handleErrorResponse();
            }
        } catch (\Exception $e) {
            return $this->handleErrorResponse($e->getMessage());
        }
    }

    public function listThreads()
    {
        return Thread::all();
    }

    public function addMessage($threadId, $message)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
                'OpenAI-Beta' => 'assistants=v2',
            ])->post("https://api.openai.com/v1/threads/{$threadId}/messages", [
                'role' => 'user',
                'content' => $message,
            ]);

            if ($response->successful()) {
                return $response->json();
            } else {
                return $this->handleErrorResponse();
            }
        } catch (\Exception $e) {
            return $this->handleErrorResponse($e->getMessage());
        }
    }

    public function runThread($threadId, $assistantId)
    {
        $client = new Client();
        $events = [];

        try {
            $response = $client->post("https://api.openai.com/v1/threads/{$threadId}/runs", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                    'OpenAI-Beta' => 'assistants=v2',
                ],
                'json' => [
                    'assistant_id' => $assistantId,
                    'stream' => true
                ],
                'stream' => true,
            ]);
            $body = $response->getBody();
            $buffer = '';

            while (!$body->eof()) {
                $buffer .= $body->read(1024);
                while (($pos = strpos($buffer, "\n")) !== false) {
                    $line = trim(substr($buffer, 0, $pos));
                    $buffer = substr($buffer, $pos + 1);

                    if (!empty($line)) {
                        if (strpos($line, 'data: ') === 0) {
                            $eventData = substr($line, 6);
                            $events[] = json_decode($eventData, true);
                        }
                    }
                }
            }

            return $events;
        } catch (RequestException $e) {
            return $this->handleErrorResponse($e->getMessage());
        } catch (\Exception $e) {
            return $this->handleErrorResponse($e->getMessage());
        }
    }

    private function handleErrorResponse($errorMessage = 'Error en la comunicación con OpenAI')
    {
        return $errorMessage;
    }
}
