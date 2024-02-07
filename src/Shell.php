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
                '--execute' => 'Execute the command.'
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
        $prompt .= ". Provide only shell code as output. Current shell is: $shell";
        $params['prompt'] = $prompt;
        $params['model'] = 'gpt-3.5-turbo';

        $result = $this->getCompletionsStream($params);

        if ($result->isError()) {
            echo $result->error_message . PHP_EOL;
            return 1;
        }

        echo PHP_EOL;

        if ($parse_argv->getOption('execute')) {
            if ($this->utils->readlineConfirm("Execute, are you sure?")) {
                passthru($result->content);
            }
        }

        return 0;
    }
}
