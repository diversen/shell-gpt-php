<?php

namespace Diversen\GPT;

use \Diversen\GPT\Base;

class Shell extends Base
{

    public function getCommand()
    {
        return [
            'usage' => 'Command to generate shell commands',
            'options' => [
                ...$this->base_options,
            ],
            'arguments' => [
                'Prompt' => 'The prompt to generate completions for.',
            ]
        ];
    }

    public function runCommand(\Diversen\ParseArgv $parse_argv)
    {

        $params = $this->getBaseParams($parse_argv);
        $prompt = $this->getPromptArgument($parse_argv);
        if ($prompt === false) {
            print("No prompt given. Please specify your prompt. " . PHP_EOL);
            return 10;
        }

        $shell = getenv('SHELL') ?? 'unknown';
        $final_prompt = <<<EOF
You are using the $shell shell.
Make a shell command that will: $prompt
EOF;

        $params['prompt'] = $final_prompt;
        $params['model'] = 'gpt-3.5-turbo-instruct';

        $result = $this->getCompletionsStream($params);

        if ($result->isError()) {
            echo $result->error_message . PHP_EOL;
            return 1;
        }

        echo PHP_EOL;

        return 0;
    }
}
