<?php

namespace VigStudio\VigAutoTranslations\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Illuminate\Routing\Events\RouteMatched;

class VigAutoTranslationsServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function register(): void
    {
        $this->setNamespace('plugins/vig-auto-translations')->loadHelpers();
    }

    public function boot(): void
    {
        $this
            ->loadAndPublishConfigurations(['permissions'])
            ->loadAndPublishTranslations()
            ->loadAndPublishViews()
            ->loadRoutes();

        $this->app->booted(function () {
            $this->app->register(HookServiceProvider::class);
        });

        Event::listen(RouteMatched::class, function () {
            dashboard_menu()->registerItem([
                'id' => 'cms-plugins-vig-auto-translations',
                'priority' => 5,
                'parent_id' => 'cms-plugin-translation',
                'name' => 'plugins/vig-auto-translations::vig-auto-translations.name_theme',
                'icon' => null,
                'url' => route('vig-auto-translations.theme'),
                'permissions' => ['vig-auto-translations.index'],
            ]);
        });
    }
}
