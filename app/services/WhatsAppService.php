<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class WhatsAppService
{
    /**
     * Kirim teks via gateway fath.my.id
     */
    public function sendText(string $phone, string $text): bool
    {
        $phone  = $this->normalize($phone);
        $url    = config('services.wa_gateway.url');
        $apiKey = config('services.wa_gateway.api_key');
        $sender = config('services.wa_gateway.sender');

        if (! $url || ! $apiKey || ! $sender || ! $phone || trim($text) === '') {
            return false;
        }

        // API ini menerima POST (form-url-encoded). (GET juga ada, tapi kita pakai POST.)
        $payload = [
            'api_key' => $apiKey,
            'sender'  => $sender,
            'number'  => $phone,
            'message' => $text,
        ];

        $res = Http::asForm()->post($url, $payload);

        // Banyak gateway balikin { status: "success" } — tapi kita anggap 2xx = sukses.
        if ($res->successful()) {
            $json = $res->json();
            return is_array($json) ? (strtolower((string)($json['status'] ?? 'success')) === 'success') : true;
        }

        return false;
    }

    /**
     * Normalisasi: 08xxxx -> 62xxxx, buang non-digit
     */
    private function normalize(string $raw): string
    {
        $raw = preg_replace('/\D+/', '', $raw ?? '');
        if ($raw === '') return '';

        if (Str::startsWith($raw, '0')) {
            $raw = '62' . ltrim($raw, '0');
        }

        return $raw;
    }
}
