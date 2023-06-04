<?php

namespace VigStudio\VigAutoTranslations\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use VigStudio\VigAutoTranslations\Manager;

#[AsCommand('vig-translate:auto', 'Auto translate from English to a new language')]
class AutoTranslateCommand extends Command
{
    public function handle(): int
    {
        if (! preg_match('/^[a-z0-9\-]+$/i', $this->argument('locale'))) {
            $this->components->error('Only alphabetic characters are allowed.');

            return self::FAILURE;
        }

        $locale = $this->argument('locale');

        $manager = app(Manager::class);

        $translations = $manager->getThemeTranslations($locale);

        $this->info(sprintf('Translating %d words.', count($translations)));

        $count = 0;
        foreach ($translations as $key => $translation) {
            if ($key != $translation) {
                continue;
            }

            $this->info(sprintf('Translating "%s"', $key));

            $translations[$key] = $manager->translate('en', $locale, $key);

            $count++;
        }

        $manager->saveThemeTranslations($locale, $translations);

        $this->components->info(sprintf('Done! %d has been translated.', $count));

        return self::SUCCESS;
    }

    protected function configure(): void
    {
        $this->addArgument('locale', InputArgument::REQUIRED, 'The locale name that you want to translate');
    }
}
