<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Session;

class LanguageSwitcher extends Component
{
    public $currentLocale;

    public function mount()
    {
        $this->currentLocale = app()->getLocale();
    }

    public function switchLanguage($locale)
    {
        // Simpan locale ke session
        Session::put('locale', $locale);

        // Update currentLocale untuk Livewire state
        $this->currentLocale = $locale;

        // Redirect ke halaman saat ini agar bahasa berubah
        return redirect(request()->fullUrl());
    }

    public function render()
    {
        return view('livewire.language-switcher');
    }
}
