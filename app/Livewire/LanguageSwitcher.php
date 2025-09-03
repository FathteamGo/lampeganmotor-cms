<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Session;
use Filament\Notifications\Notification;

class LanguageSwitcher extends Component
{
    public $currentLocale;

    public function mount()
    {
        $this->currentLocale = app()->getLocale();
    }

    public function switchLanguage($locale)
    {
        Session::put('locale', $locale);
        
        // Langsung redirect ke halaman saat ini dengan locale baru
        return redirect(request()->fullUrl());
    }

    public function render()
    {
        return view('livewire.language-switcher');
    }
}