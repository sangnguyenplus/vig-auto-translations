<?php

namespace VigStudio\VigAutoTranslations\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Str;

/**
 * Use the AWS Translate service
 *
 * https://aws.amazon.com/translate/
 */
class ChatGPTTranslator
{
    /**
     * @inheritDoc
     */
    public function translate(string $line, string $to, string $from): ?string
    {
        $api_key = setting('vig_translate_chatgpt_key', config('plugins.vig-auto-translations.general.chatgpt_key'));
        $client = new Client();

        //Make a POST request to the OpenAI API
        $response = $client->post('https://api.openai.com/v1/completions', [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $api_key,
            ],
            'json' => [
                'model' => 'text-davinci-003',
                'prompt' => 'translations "' . $line . '" to ' . $to . ' from ' . $from,
                'max_tokens' => 1000,
            ],
        ]);

        // Get the JSON response
        $responseJson = json_decode($response->getBody()->getContents());

        if ($responseJson) {
            $result = (string) $responseJson->choices[0]->text;

            return Str::squish($result);
        }

        return null;
    }
}
