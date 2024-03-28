<?php

namespace VigStudio\VigAutoTranslations;

use Illuminate\Support\Facades\File;
use Throwable;

class Dictionary
{
    protected string $locale;

    public function locale(string $locale): static
    {
        $this->locale = $locale;

        return $this;
    }

    public function getTranslate(string $text): string
    {
        try {
            $path = sprintf(__DIR__ . '/../resources/dictionaries/%s.json', $this->locale);

            if (! File::exists($path)) {
                return $text;
            }

            $dictionary = File::json($path);

            return $dictionary[$text] ?? $text;
        } catch (Throwable) {
            return $text;
        }
    }
}
