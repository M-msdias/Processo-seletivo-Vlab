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
                Log::error('AI Request failed', [
                    'title'   => $title,
                    'type'    => $type->value,
                    'status'  => $response->status(),
                    'body'    => $response->body(),
                ]);
                throw new \Exception('Falha ao consultar a API de IA.');
            }

            $content = $response->json('candidates.0.content.parts.0.text');
            $tokenUsage = $response->json('usageMetadata.totalTokenCount', 0);

            Log::info('AI Request', [
                'title'       => $title,
                'type'        => $type->value,
                'TokenUsage'  => $tokenUsage,
                'Latency'     => "{$latency}s",
            ]);

            return json_decode($content, true);

        } catch (ConnectionException $e) {
            Log::error('AI Connection timeout', ['title' => $title]);
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
