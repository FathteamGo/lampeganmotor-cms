<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class WhatsAppService
{
    /**
     * Kirim teks via gateway wa2.fath.my.id dengan Basic Auth
     */
    public function sendText(string $phone, string $text): bool
    {
        $phone    = $this->normalize($phone);
        $url      = config('services.wa_gateway.url');
        $username = config('services.wa_gateway.username');
        $password = config('services.wa_gateway.password');

        if (!$url || !$username || !$password || !$phone || trim($text) === '') {
            return false;
        }

        // Siapkan payload JSON untuk endpoint wa2.fath.my.id
        $payload = [
            'phone'   => $phone,
            'message' => $text,
        ];

        try {
            // Kirim request dengan Basic Auth
            $res = Http::withBasicAuth($username, $password)
                       ->withHeaders(['Content-Type' => 'application/json'])
                       ->post($url, $payload);

            // Cek response sukses
            if ($res->successful()) {
                $responseBody = $res->json();

                // Cek status dalam response
                if (is_array($responseBody)) {
                    $status = strtolower((string)($responseBody['code'] ?? $responseBody['status'] ?? ''));
                    if (in_array($status, ['success', 'ok', 'sent', 'true', '200'])) {
                        return true;
                    }
                }

                // Fallback: jika 2xx response dianggap sukses
                return true;
            }

            return false;
        } catch (\Throwable $e) {
            \Log::error('WhatsApp Service Exception: ' . $e->getMessage());
            return false;
        }
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
