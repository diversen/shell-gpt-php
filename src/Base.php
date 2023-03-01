<?php

namespace Diversen\GPT;

use Diversen\Cli\Utils;
use Diversen\Spinner;
use Exception;

class Base
{

    public $utils = null;

    public array $baseOptions = [
        '--model' => 'GPT-3 model name. davinci, curie, babbage, ada',
        '--max-tokens' => 'Strict length of output (words).',
        '--temperature' => 'Temperature of output. Between 0 and 2. Higher value is more random',
        '--top-p' => 'Top p of output. Between 0 and 1. Higher value is more random',
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
        $models = [
            'davinci' => 'text-davinci-003',
            'curie' => 'text-curie-001',
            'babbage' => 'text-babbage-001',
            'ada' => 'text-ada-001',
            'code-davinci' => 'code-davinci-002', // Experimental
        ];

        $model = $parse_argv->getOption('model') ?? 'davinci';
        $model = $models[$model] ?? $model;

        $max_tokens = $parse_argv->getOption('max-tokens') ?? 2048;
        $temperature = $parse_argv->getOption('temperature') ?? 0.2;
        $top_p = $parse_argv->getOption('top-p') ?? 0.9;
        
        $params = [
            'model' => $model,
            'max_tokens' => (int) $max_tokens,
            'temperature' => (float) $temperature,
            'top_p' => (float) $top_p,
        ];

        return $params;
    }

    public function openAiRequest($params)
    {

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
            throw new Exception('Request error:' . curl_error($ch));
        }

        
        curl_close($ch);

        return $result;
    }

    public function getResult(array $params)
    {

        // Output is to STDOUT
        $spinner = new Spinner(spinner:'dots');
        $result = $spinner->callback(function() use ($params) {
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
        $text = trim($result["choices"][0]["text"]);
        return $text;
    }
}
