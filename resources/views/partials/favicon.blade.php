@php
    $favicon = \App\Models\Favicon::latest()->first();
@endphp

<link rel="icon" type="image/png" href="{{ $favicon?->path ? asset('storage/' . $favicon->path) : asset('favicon.ico') }}">
