<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    protected string $endpoint;

    public function __construct()
    {
        $model = config('services.gemini.model', env('GEMINI_MODEL'));
        $this->endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent";
    }

    public function generate(string $prompt): string
    {
        try {
            $res = Http::withHeaders([
                'Content-Type' => 'application/json',
                'X-goog-api-key' => config('services.gemini.key', env('GEMINI_API_KEY')),
            ])->post($this->endpoint, [
                'contents' => [
                    ['parts' => [['text' => $prompt]]]
                ]
            ]);

            if ($res->failed()) {
                Log::error('Gemini failed', ['status' => $res->status(), 'body' => $res->body()]);
                return 'ERROR: Gemini failed: ' . $res->body();
            }

            $json = $res->json();
            return $json['candidates'][0]['content']['parts'][0]['text'] ?? json_encode($json);
        } catch (\Throwable $e) {
            Log::error('Gemini exception: ' . $e->getMessage());
            return 'ERROR: ' . $e->getMessage();
        }
    }
}
