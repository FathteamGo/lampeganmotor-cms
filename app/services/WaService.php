<?php

namespace App\Services;

use App\Models\WhatsAppNumber;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class WaService
{
    /**
     * Kirim teks via gateway fath.my.id
     */
    public function sendText(string $phone, string $text): bool
    {
     
        $url    = config('services.wa_gateway.url');
        $apiKey = config('services.wa_gateway.api_key');
        $sender = config('services.wa_gateway.sender');
        // Ambil nomor sender dari DB dulu, fallback ke .env
        $phone = WhatsAppNumber::where('is_active', true)
                    ->where('is_report_gateway', true)
                    ->value('number') ?? config('services.wa_gateway.sender');

        if (! $url || ! $apiKey || ! $sender || ! $phone || trim($text) === '') {
            $msg = "WA Service: Konfigurasi tidak lengkap atau data kosong.";
            \Log::warning($msg, [
                'url'    => $url,
                'apiKey' => $apiKey ? 'SET' : 'EMPTY',
                'sender' => $sender ?: 'EMPTY',
                'phone'  => $phone,
            ]);
            echo $msg . "\n";
            return false;
        }

        $payload = [
            'api_key' => $apiKey,
            'sender'  => $this->normalize($sender),
            'number'  => $phone,
            'message' => $text,
        ];

        // Kirim request
        $res = Http::asForm()->post($url, $payload);

        // Debug: log & echo payload + response
        \Log::info('WA Payload Debug', $payload);
        \Log::info('WA Response Debug', ['status' => $res->status(), 'body' => $res->body()]);
        echo "Payload: " . json_encode($payload, JSON_UNESCAPED_UNICODE) . "\n";
        echo "Response: " . $res->body() . "\n";

        if ($res->successful()) {
            $body = $res->body();
            $json = json_decode($body, true);

            if (is_array($json)) {
                $status = strtolower((string)($json['status'] ?? ''));
                if (in_array($status, ['success', 'ok', 'sent', 'true'])) {
                    return true;
                }
            }

            // fallback: kalau bukan JSON tapi ada body → sukses
            if (! empty($body)) {
                return true;
            }
        }

        echo "❌ Gagal mengirim WA\n";
        \Log::error('WA Service: gagal mengirim', ['payload' => $payload, 'response' => $res->body()]);

        return false;
    }

    /**
     * Normalisasi: 08xxxx -> 62xxxx, buang non-digit
     */
    private function normalize(?string $raw): string
    {
        $raw = preg_replace('/\D+/', '', $raw ?? '');
        if ($raw === '') return '';

        if (Str::startsWith($raw, '0')) {
            $raw = '62' . ltrim($raw, '0');
        }

        return $raw;
    }
}
