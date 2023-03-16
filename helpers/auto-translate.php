<?php

use VigStudio\VigAutoTranslations\Services\GoogleTranslate;
use VigStudio\VigAutoTranslations\Services\AWSTranslator;

if (! function_exists('vig_auto_translate')) {
    function vig_auto_translate(string $source = 'en', string $target, string $value): string|null
    {
        if (setting('vig_translate_driver') == 'google' || empty(setting('vig_translate_driver'))) {
            $translation = new GoogleTranslate();
            $result = $translation->setSource($source)
                        ->setTarget($target)
                        ->translate($value);
        } else {
            $translation = new AWSTranslator();
            $result = $translation->translate($value, $target, $source);
        }

        return $result;
    }
}
