<?php

namespace Diversen\GPT;

use Diversen\Cli\Utils;
use Exception;

class Base
{

    public $utils = null;

    public array $baseOptions = [
        '--model' => 'GPT-3 model name. davinci, curie, babbage, ada',
        '--max-tokens' => 'Strict length of output (words).',
        '--temperature' => 'Temperature of output.',
        '--top-p' => 'Top p of output.',
    ];

    public function __construct()
    {
        $this->utils = new Utils();
    }

    private function getKeyFile() {
        $home = getenv("HOME");
        $file = $home . '/.config/shell-gpt/api_key.txt';
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

        $models = [
            'davinci' => 'text-davinci-003',
            'curie' => 'text-curie-001',
            'babbage' => 'text-babbage-001',
            'ada' => 'text-ada-001',
        ];

        $model = $parse_argv->getOption('model') ?? 'davinci';
        $model = $models[$model] ?? $model;

        $params = [
            'model' => $model,
            'max_tokens' => $parse_argv->getOption('max-tokens') ?? 2048,
            'temperature' => $parse_argv->getOption('temperature') ?? 0.2,
            'top_p' => $parse_argv->getOption('top-p') ?? 0.9,
        ];

        return $params;
    }

    public function openAiRequest($params)
    {

        // Send request to OpenAI API
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.openai.com/v1/completions');
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
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);

        return $result;
    }

    public function getResult(array $params)
    {
        $result = $this->openAiRequest($params);
        $result = json_decode($result, true);
        $error = $result["error"] ?? null;

        if ($error) {
            print($result["error"]["message"] . PHP_EOL);
            print("You may also check existing key file: ". $this->getKeyFile() . PHP_EOL);
            exit(1);
        }
        $text = trim($result["choices"][0]["text"]);
        return $text;
    }
}
