<?php

namespace VigStudio\VigAutoTranslations;

use Botble\PluginManagement\Abstracts\PluginOperationAbstract;

class Plugin extends PluginOperationAbstract
{
    public static function remove(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('vig_translations');
    }
}
