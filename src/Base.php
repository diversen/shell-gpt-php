<?php

namespace Diversen\GPT;

use Diversen\Cli\Utils;
use Diversen\GPT\OpenAiApi;
use Diversen\GPT\ApiResult;

class Base
{

    public ?Utils $utils = null;
    public string $base_dir = '';
    public string $data_dir = '';
    public string $params_file = '';

    public array $base_options = [
        '--max_tokens' => 'Strict length of output (words).',
        '--temperature' => 'Temperature of output. Between 0 and 2. Higher value is more random',
        '--top_p' => 'Top p of output. Between 0 and 1. Higher value is more random',
        '--presence_penalty' => 'Presence penalty of output. Between -2 and 2. Higher value insures more unique output',

    ];

    public array $default_options = [
        "max_tokens" => 2048,
        "temperature" => 1,
        "top_p" => 0.5,
        "n" => 1,
        "presence_penalty" => 0,
        "stream" => false,
        // "logprobs" => null,
        // "stop" => "\n",
    ];

    public function __construct()
    {
        $this->utils = new Utils();
        $this->base_dir = getenv("HOME") . '/.config/shell-gpt-php';
        if (!file_exists($this->base_dir)) {
            mkdir($this->base_dir, 0755, true);
        }
        $this->data_dir = $this->base_dir . '/data';
        if (!file_exists($this->data_dir)) {
            mkdir($this->data_dir, 0755, true);
        }

        $this->params_file = $this->base_dir . '/params.json';
    }

    public function castOptions(string $key, mixed $value)
    {
        if (in_array($key, ['temperature', 'top_p', 'presence_penalty'])) {
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

    /**
     * Merge default options with options from params file
     */
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

    /**
     * Get params from default options and command line options
     */
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

    private function getOpenApi(): OpenAiApi
    {
        $openai_api = new OpenAiApi(
            api_key: $this->getApiKey(), 
            stream_sleep: 0.05
        );
        return $openai_api;
    } 

    public function getCompletions(array $params): ApiResult
    {

        $openai_api = $this->getOpenApi();
        $result = $openai_api->getCompletions($params);
        $this->logTokensUsed($result->tokens_used);
        return $result;
    }

    public function getCompletionsStream(array $params): ApiResult
    {

        $openai_api = $this->getOpenApi();
        $result = $openai_api->getCompletionsStream($params);
        $this->logTokensUsed($result->tokens_used);
        return $result;
    }

    public function getChatCompletions(array $params): ApiResult
    {
        $openai_api = $this->getOpenApi();
        $result = $openai_api->getChatCompletions($params);
        $this->logTokensUsed($result->tokens_used);
        return $result;
    }

    public function getChatCompletionsStream(array $params): ApiResult
    {
        $openai_api = $this->getOpenApi();
        $result = $openai_api->getChatCompletionsStream($params);
        $this->logTokensUsed($result->tokens_used);
        return $result;
    }

    public function getTokensUsedLine(int $tokens)
    {
        $tokens = (string)$tokens;
        return " (tokens used: $tokens) ";
    }

    public function getPromptArgument(\Diversen\ParseArgv $parse_argv)
    {
        
        if (!$parse_argv->getArgument(0)) {
            return false;
        }

        $prompt = trim(implode(" ", $parse_argv->arguments));
        
        $stdin = $this->utils->readStdin();
        if($stdin) {
            $prompt = $stdin . PHP_EOL . PHP_EOL . $prompt;
        }
        
        return $prompt;
    }

    private function logTokensUsed(int $tokens)
    {
        $file = $this->base_dir . '/tokens_used.txt';

        if (!file_exists($file)) {
            file_put_contents($file, '');
        }

        $content = time() . "," . (string)$tokens . PHP_EOL;
        file_put_contents($file, $content, FILE_APPEND);
    }

    public function getDialogAsTxt(array $messages)
    {
        $dialog = '';
        foreach ($messages as $message) {
            $role = $message['role'];
            $content = $message['content'];
            $dialog .=  ucfirst($role) . ': ' . $content . PHP_EOL . PHP_EOL;
        }
        return $dialog;
    }
}
