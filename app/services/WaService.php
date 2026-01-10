<?php

namespace App\Services;

use App\Models\WhatsAppNumber;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class WaService
{
    /**
     * Kirim teks via gateway wa2.fath.my.id dengan Basic Auth
     */
    public function sendText(string $phone, string $text): bool
    {
        $url      = config('services.wa_gateway.url');
        $username = config('services.wa_gateway.username');
        $password = config('services.wa_gateway.password');

        // Ambil nomor penerima dari DB (nomor gateway)
        $recipientNumber = WhatsAppNumber::where('is_active', true)
                    ->where('is_report_gateway', true)
                    ->value('number');

        if (!$url || !$username || !$password || !$recipientNumber || trim($text) === '') {
            $msg = "WA Service: Konfigurasi tidak lengkap atau data kosong.";
            \Log::warning($msg, [
                'url'      => $url,
                'username' => $username ? 'SET' : 'EMPTY',
                'password' => $password ? 'SET' : 'EMPTY',
                'recipientNumber' => $recipientNumber ?: 'EMPTY',
            ]);
            return false;
        }

        // Siapkan payload JSON untuk endpoint wa2.fath.my.id
        $payload = [
            'phone' => $recipientNumber,
            'message' => $text,
        ];

        try {
            // Kirim request dengan Basic Auth
            $res = Http::withBasicAuth($username, $password)
                       ->withHeaders(['Content-Type' => 'application/json'])
                       ->post($url, $payload);

            // Log untuk debugging
            \Log::info('WA Send Request', [
                'url' => $url,
                'phone' => $recipientNumber,
                'status' => $res->status(),
            ]);
            \Log::info('WA Response', ['body' => $res->body()]);

            // Cek response
            if ($res->successful()) {
                $responseBody = $res->json();

                // Cek status dalam response (sesuaikan dengan response format API)
                if (is_array($responseBody)) {
                    $status = strtolower((string)($responseBody['code'] ?? $responseBody['status'] ?? ''));
                    if (in_array($status, ['success', 'ok', 'sent', 'true', '200'])) {
                        return true;
                    }
                }

                // Fallback: jika 2xx response dianggap sukses
                return true;
            }

            \Log::error('WA Service: gagal mengirim', [
                'status' => $res->status(),
                'response' => $res->body(),
            ]);

            return false;
        } catch (\Throwable $e) {
            \Log::error('WA Service Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return false;
        }
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
