<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenRouterService implements AiServiceInterface
{
    protected string $endpoint;
    protected string $apiKey;
    protected string $model;

    public function __construct()
    {
        $this->endpoint = config('services.openrouter.endpoint');
        $this->apiKey = config('services.openrouter.api_key');
        $this->model = config('services.openrouter.model');
    }

    /**
     * Generate text using OpenRouter. If the request fails, fall back to Gemini.
     */
    public function generate(string $prompt): string
    {
        try {
            $res = Http::timeout(60)->withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => "Bearer {$this->apiKey}",
            ])->post($this->endpoint, [
                'model' => $this->model,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
                'max_tokens' => 1024,
            ]);

            if ($res->failed()) {
                Log::warning('OpenRouter failed, falling back to Gemini', ['status' => $res->status(), 'body' => $res->body()]);
                return $this->fallbackToGemini($prompt);
            }

            $data = $res->json();
            // OpenRouter's response format may vary; attempt to extract content.
            $content = $data['choices'][0]['message']['content'] ?? null;
            if ($content) {
                return $content;
            }
            // Unexpected format, fallback.
            Log::warning('OpenRouter unexpected response format, falling back to Gemini', ['response' => $data]);
            return $this->fallbackToGemini($prompt);
        } catch (\Throwable $e) {
            Log::error('OpenRouter exception, fallback to Gemini', ['message' => $e->getMessage()]);
            return $this->fallbackToGemini($prompt);
        }
    }

    protected function fallbackToGemini(string $prompt): string
    {
        return app(GeminiService::class)->generate($prompt);
    }
}
