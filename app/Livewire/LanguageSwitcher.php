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
        if (in_array($locale, ['id', 'en'])) {
            Session::put('locale', $locale);
            app()->setLocale($locale);
            $this->currentLocale = $locale;
            
            // Notification untuk konfirmasi
            Notification::make()
                ->title($locale === 'id' ? 'Bahasa berhasil diubah' : 'Language changed successfully')
                ->success()
                ->send();

            // Refresh halaman untuk apply language change
            return redirect(request()->header('Referer') ?: '/admin');
        }
    }

    public function render()
    {
        return view('livewire.language-switcher');
    }
}