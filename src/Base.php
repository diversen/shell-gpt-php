<?php

namespace Diversen\GPT;

use Diversen\Cli\Utils;
use Diversen\Spinner;
use Exception;

class Result
{
    public string $tokens_used = '0';
    public string $content;
    public ?string $error = null;
}

class Base
{

    public $timeout = 60;
    public ?Utils $utils = null;
    public string $endpoint = '';
    public $base_dir = '';
    private string $api_key = '';
    public ?string $error = null;

    public array $baseOptions = [
        '--model' => 'GPT-3 model name. text-davinci-003, text-curie-001 etc. See: https://beta.openai.com/docs/api-reference/models',
        '--max-tokens' => 'Strict length of output (words).',
        '--temperature' => 'Temperature of output. Between 0 and 2. Higher value is more random',
        '--top-p' => 'Top p of output. Between 0 and 1. Higher value is more random',
        '--timeout' => 'Timeout in seconds. Default is 60 seconds',
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

    private function setApiKey()
    {
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
            unset($this->defaultOptions['top_p']);
        }

        if ($parse_argv->getOption('top-p')) {
            $this->defaultOptions['top_p'] = (float) $parse_argv->getOption('top-p');
            unset($this->defaultOptions['temperature']);
        }

        if ($parse_argv->getOption('max-tokens')) {
            $this->defaultOptions['max_tokens'] = (int) $parse_argv->getOption('max-tokens');
        }

        if ($parse_argv->getOption('timeout')) {
            $this->timeout = (int) $parse_argv->getOption('timeout');
        }

        return $this->defaultOptions;
    }

    /**
     * Use multi-curl in order to make the request non blocking
     * Easier to abort the request
     */
    private function openAiRequest($params)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);

        $headers = array();
        $headers[] = 'Content-Type: application/json';
        $headers[] = 'Authorization: Bearer ' . $this->api_key;

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $mh = curl_multi_init();
        curl_multi_add_handle($mh, $ch);

        do {
            $status = curl_multi_exec($mh, $active);
        } while ($status === CURLM_CALL_MULTI_PERFORM || $active);

        while ($active && $status === CURLM_OK) {
            if (curl_multi_select($mh) === -1) {
                usleep(100);
            }
            do {
                $status = curl_multi_exec($mh, $active);
            } while ($status === CURLM_CALL_MULTI_PERFORM);
        }

        if ($status !== CURLM_OK) {
            throw new Exception(curl_multi_strerror($status));
        }

        $result = curl_multi_getcontent($ch);

        curl_multi_remove_handle($mh, $ch);
        curl_multi_close($mh);
        curl_close($ch);

        return $result;
    }

    /**
     * Checks if the result is valid
     */
    private function validateResult($result)
    {
        $result = json_decode($result, true);
        $error = $result["error"] ?? null;

        if ($error) {
            $this->error = "API ERROR: " . $result["error"]["message"];
        }

        return $result;
    }

    public function getApiResult(array $params)
    {

        $this->setApiKey();

        $spinner = new Spinner(spinner: 'simpleDots');
        $result = $spinner->callback(function () use ($params) {
            try {
                $result = $this->openAiRequest($params);
                if ($result === '') {
                    $this->error = "API Error: Request timed out";
                }
                return $result;
            } catch (Exception $e) {
                $this->error = "Request Error" . $e->getMessage();
            }
        });

        $result = $this->validateResult($result);
        return $result;
    }

    public function getCompletions(array $params): Result
    {

        $this->endpoint = 'https://api.openai.com/v1/completions';
        $api_result = $this->getApiResult($params);
        $result = new Result();

        if (!$this->error) {
            $result->tokens_used = $api_result["usage"]["total_tokens"];
            $result->content = trim($api_result["choices"][0]["text"]);
            $this->logTokensUsed($api_result["usage"]["total_tokens"]);
        } else {
            $result->content = $this->error;
        }

        return $result;
    }

    public function getChatCompletions(array $params): Result
    {
        $this->endpoint = 'https://api.openai.com/v1/chat/completions';
        $api_result = $this->getApiResult($params);
        $result = new Result();
        
        if (!$this->error) {
            $result->tokens_used = $api_result["usage"]["total_tokens"];
            $result->content = trim($api_result["choices"][0]["message"]["content"]);
            $this->logTokensUsed($api_result["usage"]["total_tokens"]);
        } else {
            $result->content = $this->error;
        }

        return $result;
    }

    public function getTokensUsedLine(string $tokens)
    {
        return " (tokens used: $tokens) ";
    }

    public function getPromptArgument(\Diversen\ParseArgv $parse_argv)
    {
        if (!$parse_argv->getArgument(0)) {
            print("No prompt given. Please specify your prompt. " . PHP_EOL);
            exit(1);
        }

        $prompt = trim(implode(" ", $parse_argv->arguments));
        return $prompt;
    }

    private function logTokensUsed(string $tokens)
    {
        $file = $this->base_dir . '/tokens_used.txt';

        if (!file_exists($file)) {
            file_put_contents($file, '');
        }

        $content = time() . "," . $tokens . PHP_EOL;
        file_put_contents($file, $content, FILE_APPEND);
    }
}
