<?php

namespace App\Http\Controllers;

use App\Enums\ResourceType;
use App\Services\SmartAssistService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'type'  => ['required', 'string', 'in:video,pdf,link'],
        ]);

        try {
            $result = $this->smartAssist->generate(
                title: $validated['title'],
                type: ResourceType::from($validated['type']),
            );

            return response()->json([
                'description' => $result['description'],
                'tags'        => $result['tags'],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 503);
        }
    }
}
