<?php

namespace VigStudio\VigAutoTranslations\Services;

use Aws\Exception\AwsException;
use Aws\Translate\TranslateClient;
use Illuminate\Support\Facades\Log;

/**
 * Use the AWS Translate service
 *
 * https://aws.amazon.com/translate/
 */
class AWSTranslator
{
    public function translate(string $line, string $to, string $from): ?string
    {
        $config = $this->loadAWSConfiguration();

        $client = new TranslateClient($config);

        try {
            $result = $client->translateText([
                'SourceLanguageCode' => $from,
                'TargetLanguageCode' => $to,
                'Text' => $line,
            ]);
            if ($result->hasKey('TranslatedText')) {
                return $result->get('TranslatedText');
            }
        } catch (AwsException $e) {
            if (config('plugins.vig-auto-translations.general.log_errors', true)) {
                Log::warning($e->getMessage());
            }
        }

        return null;
    }

    /**
     * Load the configuration to pass to AWS
     */
    private function loadAWSConfiguration(): array
    {
        return [
            'version' => config('plugins.vig-auto-translations.general.aws_key', 'latest'),
            'region' => setting('vig_translate_aws_region', config('plugins.vig-auto-translations.general.aws_region')),
            'credentials' => [
                'key' => setting('vig_translate_aws_key', config('plugins.vig-auto-translations.general.aws_key')),
                'secret' => setting('vig_translate_aws_secret', config('plugins.vig-auto-translations.general.aws_secret')),
            ],
        ];
    }
}
