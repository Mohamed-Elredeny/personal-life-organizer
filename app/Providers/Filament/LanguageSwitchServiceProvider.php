<?php

namespace App\Providers\Filament;

use BezhanSalleh\FilamentLanguageSwitch\LanguageSwitch;
use Illuminate\Support\ServiceProvider;

class LanguageSwitchServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        LanguageSwitch::configureUsing(function (LanguageSwitch $switch) {
            $switch
                ->locales(['en', 'ar'])
                ->labels([
                    'en' => 'English',
                    'ar' => 'العربية',
                ])
                ->flags([
                    'en' => 'https://flagcdn.com/w40/gb.png',
                    'ar' => 'https://flagcdn.com/w40/sa.png',
                ])
                ->displayLocale('native')
                ->circular()
                ->outsidePanelRoutes([]);
        });
    }
}
