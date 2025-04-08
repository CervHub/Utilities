<?php

namespace App\Services;

use App\Repositories\OpenAiRepository;
use Illuminate\Http\Request;
use Exception;

class OpenAiService
{
    private $openAiRepository;

    public function __construct(OpenAiRepository $openAiRepository)
    {
        $this->openAiRepository = $openAiRepository;
    }

    public function chat(Request $request)
    {
        try {
            $request->validate([
                'message' => 'required|string',
            ]);

            $message = $request->input('message');
            $response = $this->openAiRepository->chat($message);

            // Asegurarse de que solo se devuelva el mensaje limpio
            return $response;
        } catch (\Exception $e) {
            return $this->handleErrorResponse($e->getMessage());
        }
    }

    public function checkMessage(Request $request)
    {
        try {
            // Validar que el request tenga el campo 'message' y 'instructions'
            $request->validate([
                'message' => 'required|string',
                'instructions' => 'required|string',
            ]);

            $message = $request->input('message');
            $instructions = $request->input('instructions');
            $response = $this->openAiRepository->checkMessage($message, $instructions);

            // Devolver la respuesta en el campo 'message' con código de estado 200
            return response()->json(['message' => $response], 200);
        } catch (Exception $e) {
            // Manejar la excepción y devolver una respuesta de error
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function assistant(Request $request)
    {
        try {
            // Validar que el request tenga el campo 'message'
            $request->validate([
                'message' => 'required|string',
                'thread_id' => 'nullable|string',
            ]);

            $message = $request->input('message');
            $threadId = $request->input('thread_id');
            $response = $this->openAiRepository->assistant($message, $threadId);

            // Devolver la respuesta en el campo 'message' con código de estado 200
            return response()->json(['message' => $response], 200);
        } catch (Exception $e) {
            // Manejar la excepción y devolver una respuesta de error
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function cervChat(Request $request)
    {
        try {
            // Validar que el request tenga el campo 'message'
            $request->validate([
                'message' => 'required|string',
                'thread_id' => 'nullable|string'
            ]);

            $message = $request->input('message');
            $threadId = $request->input('thread_id');
            $response = $this->openAiRepository->cervChat($message, $threadId);

            // Devolver la respuesta en el campo 'message' con código de estado 200
            return response()->json(['message' => $response], 200);
        } catch (Exception $e) {
            // Manejar la excepción y devolver una respuesta de error
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function listAssistants(Request $request)
    {
        try {
            // Validar los parámetros opcionales 'order' y 'limit'
            $request->validate([
                'order' => 'in:asc,desc',
                'limit' => 'integer|min:1|max:100',
            ]);

            $order = $request->input('order', 'desc');
            $limit = $request->input('limit', 20);
            $response = $this->openAiRepository->listAssistants($order, $limit);

            // Devolver la respuesta con código de estado 200
            return response()->json(['assistants' => $response], 200);
        } catch (Exception $e) {
            // Manejar la excepción y devolver una respuesta de error
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function createThread(Request $request)
    {
        try {
            // Validar que el request tenga los campos 'apiKey' y 'userId'
            $request->validate([
                'apiKey' => 'required|string',
                'userId' => 'nullable|string',
            ]);

            $apiKey = $request->input('apiKey');
            $userId = $request->input('userId');
            $expiresAt = $request->input('expires_at'); // Opcional

            $thread = $this->openAiRepository->createThread($apiKey, $userId);

            // Devolver la respuesta con el thread creado y código de estado 201
            return response()->json(['thread' => $thread], 201);
        } catch (Exception $e) {
            // Manejar la excepción y devolver una respuesta de error
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function listThreads()
    {
        try {
            $threads = $this->openAiRepository->listThreads();

            // Devolver la respuesta con los threads y código de estado 200
            return response()->json(['threads' => $threads], 200);
        } catch (Exception $e) {
            // Manejar la excepción y devolver una respuesta de error
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
