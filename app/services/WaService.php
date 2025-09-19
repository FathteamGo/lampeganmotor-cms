<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class WaService
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
            \Log::warning("WA Service: Konfigurasi tidak lengkap atau data kosong.", [
                'url'    => $url,
                'apiKey' => $apiKey ? 'SET' : 'EMPTY',
                'sender' => $sender,
                'phone'  => $phone,
            ]);
            return false;
        }

        $payload = [
            'api_key' => $apiKey,
            'sender'  => $sender,
            'number'  => $phone,
            'message' => $text,
        ];

        $res = Http::asForm()->post($url, $payload);

        // Log selalu simpan response asli
        \Log::info('WA Gateway Response', [
            'phone'    => $phone,
            'payload'  => $payload,
            'status'   => $res->status(),
            'body'     => $res->body(),
        ]);

        if ($res->successful()) {
            $body = $res->body();

            // coba decode JSON
            $json = json_decode($body, true);

            if (is_array($json)) {
                $status = strtolower((string)($json['status'] ?? ''));

                // anggap sukses kalau ada salah satu keyword
                if (in_array($status, ['success', 'ok', 'sent', 'true'])) {
                    return true;
                }
            }

            // fallback: kalau bukan JSON tapi ada body → sukses
            if (! empty($body)) {
                return true;
            }
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
