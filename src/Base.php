<?php

namespace Diversen\GPT;

use Diversen\Cli\Utils;
use Diversen\GPT\OpenAiApi;
use Diversen\GPT\ApiResult;
use Diversen\Spinner;
use Diversen\GPT\Tokens;
use Throwable;

class Base
{

    public ?Utils $utils = null;
    public string $base_dir = '';

    public array $base_options = [
        '--model' => 'GPT-3 model name. text-davinci-003, text-curie-001 etc. See: https://beta.openai.com/docs/api-reference/models',
        '--max_tokens' => 'Strict length of output (words).',
        '--temperature' => 'Temperature of output. Between 0 and 2. Higher value is more random',
        '--top_p' => 'Top p of output. Between 0 and 1. Higher value is more random',
        '--timeout' => 'Timeout in seconds. Default is 60 seconds',
    ];

    public array $default_options = [
        "model" => "text-davinci-003",
        // "prompt" => "Say this is a test",
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
    }

    public function getApiKey()
    {
        $file = $this->base_dir . '/api_key.txt';
        if (file_exists($file)) {
            return trim(file_get_contents($file));
        }

        print("No openAI API key found. Use 'shgpt key' to set it." . PHP_EOL);
        exit(1);
    }

    public function getBaseParams(\Diversen\ParseArgv $parse_argv)
    {

        if ($parse_argv->getOption('model')) {
            $this->default_options['model'] = $parse_argv->getOption('model');
        }

        if ($parse_argv->getOption('top_p')) {
            $this->default_options['top_p'] = (float) $parse_argv->getOption('top_p');
            unset($this->default_options['temperature']);
        }

        if ($parse_argv->getOption('temperature')) {
            $this->default_options['temperature'] = (float) $parse_argv->getOption('temperature');
            unset($this->default_options['top_p']);
        }

        if (isset($this->default_options['temperature']) && isset($this->default_options['top_p'])) {
            unset($this->default_options['top_p']);
        }

        if ($parse_argv->getOption('max_tokens')) {
            $this->default_options['max_tokens'] = (int) $parse_argv->getOption('max_tokens');
        }

        return $this->default_options;
    }

    public function getCompletions(array $params): ApiResult
    {
        $this->getApiKey();
        $spinner = new Spinner(spinner: 'simpleDots', use_keyboard_interrupts: true);
        $result = $spinner->callback(function () use ($params) {
            $openai_api = new OpenAiApi($this->getApiKey());
            $result = $openai_api->getCompletions($params);
            $this->logTokensUsed($result->tokens_used);
            return $result;
        });

        return $result;
    }

    public function getChatCompletions(array $params): ApiResult
    {
        $this->getApiKey();
        $spinner = new Spinner(spinner: 'simpleDots');
        $result = $spinner->callback(function () use ($params) {
            $openai_api = new OpenAiApi($this->getApiKey());
            $result = $openai_api->getChatCompletions($params);
            $this->logTokensUsed($result->tokens_used);
            return $result;
        });

        return $result;
    }

    public function getChatCompletionsStream(array $params): ApiResult
    {
        $result = new ApiResult();
        $params['stream'] = true;
        $openai_api = new OpenAiApi($this->getApiKey());
        $endpoint = 'https://api.openai.com/v1/chat/completions';

        $tokens = Tokens::estimate_tokens(json_encode($params['messages']), 'max');
        $complete_response = '';

        try {
            $openai_api->openAiStream($endpoint, $params, function ($content) use (&$complete_response) {
                $complete_response .= $content;
                echo $content;
            });
        } catch (Throwable $e) {
            $result->error_code = $e->getCode();
            $result->content = $e->getMessage();
            return $result;
        }

        

        $assistant = ['role' => 'assistant', 'text' => $complete_response];
        $json_assistent = json_encode($assistant, true);
        $tokens += Tokens::estimate_tokens($json_assistent, 'max');
        $this->logTokensUsed($tokens);


        $result->setResultAsText($complete_response, $tokens);

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
