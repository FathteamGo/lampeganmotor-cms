<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class WhatsAppService
{
    public function sendText(string $phone, string $text): bool
    {
        $phone = $this->normalize($phone);
        $token = config('services.whatsapp.token');
        $from  = config('services.whatsapp.phone_number_id');

        if (! $token || ! $from || ! $phone) {
            return false;
        }

        $url = "https://graph.facebook.com/v19.0/{$from}/messages";

        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $phone,
            'type' => 'text',
            'text' => ['body' => $text],
        ];

        $res = Http::withToken($token)->post($url, $payload);

        return $res->successful();
    }

    private function normalize(string $raw): string
    {
        $raw = preg_replace('/\D+/', '', $raw ?? '');
        if ($raw === '') return '';

        // 08xxxx -> 62xxxx; 0xxxx -> 62xxxx
        if (Str::startsWith($raw, '0')) {
            $raw = ltrim($raw, '0');
            $raw = (config('services.whatsapp.default_prefix') ?: '62') . $raw;
        }

        return $raw;
    }
}
