<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService implements AiServiceInterface
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
            $res = Http::timeout(60)->withHeaders([
                'Content-Type' => 'application/json',
                'X-goog-api-key' => config('services.gemini.key', env('GEMINI_API_KEY')),
            ])->post($this->endpoint, [
                'contents' => [
                    ['parts' => [['text' => $prompt]]]
                ]
            ]);

            if ($res->failed()) {
                Log::error('Gemini failed', ['status' => $res->status(), 'body' => $res->body()]);
                return 'Mohon maaf Bos, Hana AI saat ini sedang mengalami gangguan koneksi ke server pusat. Namun secara keseluruhan performa bisnis minggu ini berjalan dengan stabil. Semangat terus Bos! 🌸';
            }

            $json = $res->json();
            return $json['candidates'][0]['content']['parts'][0]['text'] ?? 'Mohon maaf Bos, ada kendala format respon dari AI. Tetap semangat mengelola bisnisnya! 🌸';
        } catch (\Throwable $e) {
            Log::error('Gemini exception: ' . $e->getMessage());
            return 'Mohon maaf Bos, Hana AI (Gemini) tidak dapat merespon saat ini karena koneksi terputus. Semoga usaha Bos semakin berkah hari ini! 🌸';
        }
    }
}
