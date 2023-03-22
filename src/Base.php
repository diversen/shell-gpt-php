<?php

namespace Diversen\GPT;

use Diversen\Cli\Utils;
use Diversen\GPT\OpenAiApi;
use Diversen\GPT\ApiResult;

class Base
{

    public ?Utils $utils = null;
    public string $base_dir = '';
    public string $params_file = '';

    public array $base_options = [
        '--max_tokens' => 'Strict length of output (words).',
        '--temperature' => 'Temperature of output. Between 0 and 2. Higher value is more random',
        '--top_p' => 'Top p of output. Between 0 and 1. Higher value is more random',
        '--timeout' => 'Timeout in seconds. Default is 60 seconds',

    ];

    public array $default_options = [
        "max_tokens" => 2048,
        "temperature" => 1,
        "top_p" => 0.5,
        "n" => 1,
        "stream" => false,
        // "logprobs" => null,
        // "stop" => "\n",
    ];

    public function __construct()
    {
        $this->utils = new Utils();
        $this->base_dir = getenv("HOME") . '/.config/shell-gpt';
        if (!file_exists($this->base_dir)) {
            mkdir($this->base_dir, 0755, true);
        }

        $this->params_file = $this->base_dir . '/params.json';
    }

    public function castOptions(string $key, mixed $value)
    {
        if (in_array($key, ['temperature', 'top_p', 'timeout'])) {
            return (float) $value;
        }
        if (in_array($key, ['max_tokens'])) {
            return (int) $value;
        }
        return $value;
    }

    public function getApiKeyStr(): ?string
    {
        $file = $this->base_dir . '/api_key.txt';
        if (file_exists($file)) {
            return trim(file_get_contents($file));
        }
        return null;
    }

    public function getApiKey()
    {
        $key = $this->getApiKeyStr();
        if (!$key) {
            print("No openAI API key found. Use 'shgpt key' to set it." . PHP_EOL);
            exit(1);
        }

        return $key;
    }

    public function getDefaultOptions(): array
    {
        $options = $this->default_options;
        if (file_exists($this->params_file)) {
            $json_params = file_get_contents($this->params_file);
            $params = json_decode($json_params, true);
            $options = array_merge($options, $params);
        }
        return $options;
    }

    public function getBaseParams(\Diversen\ParseArgv $parse_argv)
    {

        $options = $this->getDefaultOptions();

        if ($parse_argv->getOption('top_p')) {
            $options['top_p'] = (float) $parse_argv->getOption('top_p');
            unset($options['temperature']);
        }

        if ($parse_argv->getOption('temperature')) {
            $options['temperature'] = (float) $parse_argv->getOption('temperature');
            unset($options['top_p']);
        }

        if (isset($options['temperature']) && isset($options['top_p'])) {
            unset($options['top_p']);
        }

        if ($parse_argv->getOption('max_tokens')) {
            $options['max_tokens'] = (int) $parse_argv->getOption('max_tokens');
        }

        return $options;
    }

    public function getCompletions(array $params): ApiResult
    {

        $openai_api = new OpenAiApi($this->getApiKey());
        $result = $openai_api->getCompletions($params);
        $this->logTokensUsed($result->tokens_used);
        return $result;
    }

    public function getChatCompletions(array $params): ApiResult
    {

        $openai_api = new OpenAiApi($this->getApiKey());
        $result = $openai_api->getChatCompletions($params);
        $this->logTokensUsed($result->tokens_used);
        return $result;
    }

    public function getChatCompletionsStream(array $params): ApiResult
    {
        $openai_api = new OpenAiApi($this->getApiKey());
        $result = $openai_api->getChatCompletionsStream($params);
        $this->logTokensUsed($result->tokens_used);
        return $result;
    }

    public function getTokensUsedLine(string $tokens)
    {
        return " (tokens used: $tokens) ";
    }

    public function getPromptArgument(\Diversen\ParseArgv $parse_argv)
    {
        if (!$parse_argv->getArgument(0)) {
            return false;
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
