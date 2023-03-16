<?php

namespace VigStudio\VigAutoTranslations\Providers;

use Illuminate\Support\ServiceProvider;

class HookServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        add_filter(BASE_FILTER_AFTER_SETTING_CONTENT, [$this, 'addSettings'], 99);
    }

    public function addSettings(?string $data = null): string
    {
        return $data . view('plugins/vig-auto-translations::setting')->render();
    }
}
