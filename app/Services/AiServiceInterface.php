<?php

namespace App\Services;

interface AiServiceInterface
{
    /**
     * Generate text based on the provided prompt.
     *
     * @param string $prompt
     * @return string
     */
    public function generate(string $prompt): string;
}
