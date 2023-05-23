<?php

use VigStudio\VigAutoTranslations\Services\GoogleTranslate;
use VigStudio\VigAutoTranslations\Services\AWSTranslator;
use VigStudio\VigAutoTranslations\Services\ChatGPTTranslator;

if (! function_exists('vig_auto_translate')) {
    function vig_auto_translate(string $source, string $target, string $value): string|null
    {
        return match (setting('vig_translate_driver')) {
            'chatgpt' => (new ChatGPTTranslator())->translate($value, $target, $source),
            'aws' => (new AWSTranslator())->translate($value, $target, $source),
            default => (new GoogleTranslate())->setSource($source)
                ->setTarget($target)
                ->translate($value),
        };
    }
}
