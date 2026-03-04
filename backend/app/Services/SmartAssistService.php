<?php

namespace App\Services;

use App\Enums\ResourceType;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmartAssistService
{
    private string $apiKey;
    private string $apiUrl;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.key');
        $this->apiUrl = config('services.gemini.url');
    }

    public function generate(string $title, ResourceType $type): array
    {
        $prompt = $this->buildPrompt($title, $type);
        $startTime = microtime(true);

        try {

            Log::info('AI Request Started', [
                'title' => $title,
                'type' => $type->value,
                'timestamp' => now()->toIso8601String()
            ]);

            $response = Http::timeout(30)
                ->post("{$this->apiUrl}?key={$this->apiKey}", [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'responseMimeType' => 'application/json',
                    ]
                ]);

            $latency = round((microtime(true) - $startTime), 2);

            if ($response->failed()) {
                Log::error('AI Request Failed', [
                    'title' => $title,
                    'type' => $type->value,
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'latency' => "{$latency}s"
                ]);
                throw new \Exception('Falha ao consultar a API de IA.');
            }

            $content = $response->json('candidates.0.content.parts.0.text');
            $tokenUsage = $response->json('usageMetadata.totalTokenCount', 0);

            Log::info('AI Request: Title="' . $title . '", TokenUsage=' . $tokenUsage . ', Latency=' . $latency . 's', [
                'title' => $title,
                'type' => $type->value,
                'token_usage' => $tokenUsage,
                'latency_seconds' => $latency,
                'response_preview' => substr($content, 0, 100)
            ]);

            return json_decode($content, true);

        } catch (ConnectionException $e) {
            $latency = round((microtime(true) - $startTime), 2);
            
            Log::error('AI Request Timeout', [
                'title' => $title,
                'type' => $type->value,
                'latency' => "{$latency}s",
                'error' => $e->getMessage()
            ]);

            throw new \Exception('A IA demorou demais para responder. Tente novamente.');
        }
    }

    private function buildPrompt(string $title, ResourceType $type): string
    {
        $typeLabel = $type->label();

        return <<<PROMPT
        Você é um Assistente Pedagógico especializado em criar descrições para materiais didáticos.

        Com base no título e tipo do material abaixo, gere:
        1. Uma descrição clara e útil para alunos (máximo 2 frases)
        2. Exatamente 3 tags relevantes em português, em letras minúsculas

        Título: "{$title}"
        Tipo: "{$typeLabel}"

        Responda APENAS com JSON válido, sem texto adicional, neste formato exato:
        {
            "description": "descrição gerada aqui",
            "tags": ["tag1", "tag2", "tag3"]
        }
        PROMPT;
    }
}
