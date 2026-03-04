<?php

namespace App\Http\Controllers;

use App\Enums\ResourceType;
use App\Services\SmartAssistService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class SmartAssistController extends Controller
{
    public function __construct(
        private readonly SmartAssistService $smartAssist
    ) {}

    /**
     * @OA\Post(
     *     path="/resources/smart-assist",
     *     tags={"Smart Assist"},
     *     summary="Gerar descrição e tags com IA",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title","type"},
     *             @OA\Property(property="title", type="string", example="Introdução à Álgebra Linear"),
     *             @OA\Property(property="type", type="string", enum={"video","pdf","link"})
     *         )
     *     ),
     *     @OA\Response(response=200, description="Descrição e tags geradas com sucesso"),
     *     @OA\Response(response=422, description="Erro de validação"),
     *     @OA\Response(response=503, description="Serviço de IA indisponível")
     * )
     */
    public function generate(Request $request): JsonResponse
    {
        $startTime = microtime(true);

        try {
            Log::info('Smart Assist Request Started', [
                'data' => $request->only(['title', 'type']),
                'user_id' => auth()->id() ?? 'guest',
                'ip' => $request->ip(),
                'timestamp' => now()->toIso8601String()
            ]);

            try {
                $validated = $request->validate([
                    'title' => ['required', 'string', 'max:255'],
                    'type'  => ['required', 'string', 'in:video,pdf,link'],
                ]);

                Log::info('Smart Assist Validation Passed', [
                    'validated_data' => $validated
                ]);

            } catch (ValidationException $e) {
                Log::warning('Smart Assist Validation Failed', [
                    'errors' => $e->errors(),
                    'data' => $request->all()
                ]);

                return response()->json([
                    'message' => 'Dados inválidos para geração com IA.',
                    'errors' => $e->errors()
                ], 422);
            }

            Log::info('Calling Smart Assist Service', [
                'title' => $validated['title'],
                'type' => $validated['type']
            ]);

            $result = $this->smartAssist->generate(
                title: $validated['title'],
                type: ResourceType::from($validated['type']),
            );

            $latency = round((microtime(true) - $startTime), 2);

            Log::info('Smart Assist Completed Successfully', [
                'title' => $validated['title'],
                'type' => $validated['type'],
                'latency' => "{$latency}s",
                'tags_generated' => $result['tags'] ?? [],
                'description_length' => strlen($result['description'] ?? ''),
                'user_id' => auth()->id() ?? 'guest'
            ]);

            return response()->json([
                'description' => $result['description'],
                'tags'        => $result['tags']
            ]);

        } catch (\InvalidArgumentException $e) {
            $latency = round((microtime(true) - $startTime), 2);
            
            Log::error('Smart Assist Invalid Resource Type', [
                'type' => $request->input('type'),
                'error' => $e->getMessage(),
                'latency' => "{$latency}s"
            ]);

            return response()->json([
                'message' => 'Tipo de recurso inválido.'
            ], 422);

        } catch (\Exception $e) {
            $latency = round((microtime(true) - $startTime), 2);
            
            Log::error('Smart Assist Failed', [
                'title' => $request->input('title'),
                'type' => $request->input('type'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'latency' => "{$latency}s",
                'user_id' => auth()->id() ?? 'guest'
            ]);

            $statusCode = 503;
            $message = $e->getMessage();

            if (str_contains($e->getMessage(), 'demorou demais')) {
                $message = 'O serviço de IA está demorando mais que o esperado. Tente novamente.';
            } elseif (str_contains($e->getMessage(), 'Falha ao consultar')) {
                $message = 'Não foi possível contactar o serviço de IA no momento.';
            }

            return response()->json([
                'message' => $message,
                'error_type' => class_basename($e)
            ], $statusCode);
        }
    }
}