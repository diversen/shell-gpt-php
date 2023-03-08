<?php

namespace Diversen\GPT;

use Diversen\Cli\Utils;
use Diversen\Spinner;
use Exception;

class Base
{

    public $utils = null;
    public $endpoint = '';
    

    public array $baseOptions = [
        '--model' => 'GPT-3 model name. gpt-3.5-turbo, text-davinci-003, text-curie-001, see: https://beta.openai.com/docs/api-reference/models',
        '--max-tokens' => 'Strict length of output (words).',
        '--temperature' => 'Temperature of output. Between 0 and 2. Higher value is more random',
        '--top-p' => 'Top p of output. Between 0 and 1. Higher value is more random',
    ];

    public array $defaultOptions = [
        'model' => 'text-davinci-003',
        'max_tokens' => 2048,
        'temperature' => 1, // 0 - 2 higher value means more random
        'top_p' => 0.5, // 0 -1 higher value means more random
    ];

    public function __construct()
    {
        $this->utils = new Utils();
    }

    private function getKeyFile()
    {
        $key_dir = getenv("HOME") . '/.config/shell-gpt';
        if (!file_exists($key_dir)) {
            mkdir($key_dir, 0700, true);
        }
        $file = $key_dir . '/api_key.txt';
        return $file;
    }

    private function getApiKey()
    {
        $file = $this->getKeyFile();
        if (file_exists($file)) {
            return trim(file_get_contents($file));
        }

        $api_key = $this->utils->readSingleline("No GPT-3 API key found. Please enter a valid API key:\n");
        file_put_contents($file, $api_key);
        return $api_key;
    }

    public function getBaseParams(\Diversen\ParseArgv $parse_argv)
    {

        if ($parse_argv->getOption('model')) {
            $this->defaultOptions['model'] = $parse_argv->getOption('model');
        }

        if ($parse_argv->getOption('temperature')) {
            $this->defaultOptions['temperature'] = (float) $parse_argv->getOption('temperature');
        }

        if ($parse_argv->getOption('top-p')) {
            $this->defaultOptions['top_p'] = (float) $parse_argv->getOption('top-p');
        }

        if ($parse_argv->getOption('max-tokens')) {
            $this->defaultOptions['max_tokens'] = (int) $parse_argv->getOption('max-tokens');
        }

        return $this->defaultOptions;
    }

    private function openAiRequest($params)
    {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));

        $api_key = $this->getApiKey();
        $headers = array();
        $headers[] = 'Content-Type: application/json';
        $headers[] = 'Authorization: Bearer ' . $api_key;

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            throw new Exception('Request error:' . curl_error($ch));
        }

        curl_close($ch);

        return $result;
    }

    public function getApiResult(array $params)
    {

        $spinner = new Spinner(spinner: 'simpleDots');
        $result = $spinner->callback(function () use ($params) {
            try {
                $res = $this->openAiRequest($params);
                return $res;
            } catch (Exception $e) {
                print($e->getMessage() . PHP_EOL);
                return 1;
            }
        });

        if ($result === 1) {
            exit(1);
        }

        $result = json_decode($result, true);
        $error = $result["error"] ?? null;

        if ($error) {
            print($result["error"]["message"] . PHP_EOL);
            exit(1);
        }

        return $result;
    }

    public function getCompletions(array $params)
    {

        $this->endpoint = 'https://api.openai.com/v1/completions';
        $result = $this->getApiResult($params);
        $text = trim($result["choices"][0]["text"]);
        return $text;
    }

    public function getChatCompletion(array $params)
    {
        $this->endpoint = 'https://api.openai.com/v1/chat/completions';
        $result = $this->getApiResult($params);
        $text = trim($result["choices"][0]["message"]["content"]);
        return $text;
    }
}
