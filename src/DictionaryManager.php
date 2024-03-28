<?php

namespace VigStudio\VigAutoTranslations;

use Illuminate\Support\Facades\File;

class DictionaryManager
{
    protected string $locale;

    public function locale(string $locale): static
    {
        $this->locale = $locale;

        return $this;
    }

    public function getTranslate(string $text): string
    {
        $dictionary = File::json(sprintf(__DIR__ . '/../resources/dictionaries/%s.json', $this->locale));

        return $dictionary[$text] ?? $text;
    }
}
