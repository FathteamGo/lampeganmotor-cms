<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check session untuk locale yang dipilih user
        if (Session::has('locale')) {
            $locale = Session::get('locale');
        } else {
            // Default ke bahasa Indonesia
            $locale = 'id';
            Session::put('locale', $locale);
        }

        // Pastikan locale yang dipilih valid
        if (in_array($locale, ['id', 'en'])) {
            app()->setLocale($locale);
        } else {
            // Fallback ke default
            app()->setLocale('id');
            Session::put('locale', 'id');
        }

        return $next($request);
    }
}