<?php

namespace Diversen\GPT;

use \Diversen\GPT\Base;

class Shell extends Base
{

    public function __construct()
    {
        parent::__construct();
    }

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
        $params['prompt'] = $prompt . '. Provide only shell code as output.';

        $result = $this->getCompletions($params);
        $text = $result->content;

        if ($parse_argv->getOption('execute')) {
            if ($this->utils->readlineConfirm("Execute command: " . $text . " ?")) {
                passthru($text);
            }
        } else {
            echo $text . PHP_EOL;
        }

        if ($result->isError()) {
            exit(1);
        }
    }
}
