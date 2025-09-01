@extends('layouts.app')

@php
use Illuminate\Support\Facades\Storage;
@endphp

@section('title', 'Jual Motor - Lampegan Motor')

@section('content')
<div class="bg-gray-50 text-gray-900 dark:text-white pb-20">
    {{-- Kalau mau hero-slider, tinggal uncomment --}}
    {{-- @include('partials.hero-slider') --}}

    {{-- Video Section --}}
    @include('partials.video')

    {{-- Filter Section --}}
    @include('partials.filter')

    <div class="container mx-auto max-w-lg px-4 sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6 text-center">Form Jual Motor</h2>

            <form wire:submit.prevent="submit" class="space-y-6">
                {{ $this->form }}

                <div class="flex justify-end">
                    <x-filament::button type="submit" color="success">
                        Kirim Data
                    </x-filament::button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
