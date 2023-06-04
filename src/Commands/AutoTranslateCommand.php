<?php

namespace VigStudio\VigAutoTranslations\Commands;

use BaseHelper;
use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
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

        if (! $this->option('path')) {
            $translations = $manager->getThemeTranslations($locale);
        } else {
            $translations = BaseHelper::getFileData($this->option('path'));
        }

        $this->info(sprintf('Translating %d words.', count($translations)));

        $count = 0;
        foreach ($translations as $key => $translation) {
            if ($key != $translation) {
                continue;
            }

            $translated = $manager->translate('en', $locale, $key);

            if ($translated != $key) {
                $this->info(sprintf('Translated "%s" to "%s"', $key, $translated));

                $count++;
            }
        }

        if ($count) {
            if (! $this->option('path')) {
                $manager->saveThemeTranslations($locale, $translations);
            } else {
                BaseHelper::saveFileData(lang_path('vendor/themes/' . basename(dirname($this->option('path'), 2)) . '/' . $locale . '.json'), $translations);
            }
        }

        $this->components->info(sprintf('Done! %d has been translated.', $count));

        return self::SUCCESS;
    }

    protected function configure(): void
    {
        $this->addArgument('locale', InputArgument::REQUIRED, 'The locale name that you want to translate');
        $this->addOption('path', null, InputOption::VALUE_REQUIRED, 'Path to the original JSON file');
    }
}
