<?php

namespace Diversen\GPT;

use Diversen\Cli\Utils;
use Diversen\Spinner;
use Exception;

class Result
{
    public string $tokens_used;
    public string $content;
}

class Base
{

    public $utils = null;
    public string $endpoint = '';
    public $base_dir = '';
    private string $api_key = '';

    public array $baseOptions = [
        '--model' => 'GPT-3 model name. text-davinci-003, text-curie-001 etc. See: https://beta.openai.com/docs/api-reference/models',
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
        $this->base_dir = getenv("HOME") . '/.config/shell-gpt';
        if (!file_exists($this->base_dir)) {
            mkdir($this->base_dir, 0755, true);
        }
    }

    private function setApiKey() {
        $file = $this->base_dir . '/api_key.txt';
        if (file_exists($file)) {
            $this->api_key = trim(file_get_contents($file));
            return;
        }

        $this->api_key = $this->utils->readSingleline("No openAI API key found. Please enter a valid API key:");
        $res = file_put_contents($file, $this->api_key);
        if ($res === false) {
            throw new Exception("Could not write API key to file");
        }
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
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);


        $headers = array();
        $headers[] = 'Content-Type: application/json';
        $headers[] = 'Authorization: Bearer ' . $this->api_key;

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

        $this->setApiKey();

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

    public function getCompletions(array $params): Result
    {

        $this->endpoint = 'https://api.openai.com/v1/completions';
        $result = $this->getApiResult($params);
        $text = trim($result["choices"][0]["text"]);
        $tokens = $result["usage"]["total_tokens"];

        $result = new Result();
        $result->tokens_used = $tokens;
        $result->content = $text;

        $this->logTokensUsed($tokens);

        return $result;
    }

    public function getChatCompletion(array $params): Result
    {
        $this->endpoint = 'https://api.openai.com/v1/chat/completions';
        $result = $this->getApiResult($params);
        $text = trim($result["choices"][0]["message"]["content"]);
        $tokens = $result["usage"]["total_tokens"];

        $result = new Result();
        $result->tokens_used = $tokens;
        $result->content = $text;

        $this->logTokensUsed($tokens);

        return $result;
    }

    public function getTokensUsedLine(string $tokens)
    {
        return " (tokens used: $tokens) ";
    }

    public function getPromptArgument(\Diversen\ParseArgv $parse_argv)
    {
        if (!$parse_argv->getArgument(0)) {
            echo "No prompt given. Please specify your prompt. " . PHP_EOL;
            exit(1);
        }

        $prompt = trim(implode(" ", $parse_argv->arguments));
        return $prompt;
    }

    private function logTokensUsed(string $tokens)
    {
        $file = $this->base_dir . '/tokens_used.txt';

        if (!file_exists($file)) {
            echo "Does not exist";
            file_put_contents($file, '');
        }

        $content = time() . "," . $tokens . PHP_EOL;
        file_put_contents($file, $content, FILE_APPEND);
    }
}
