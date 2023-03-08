<?php

namespace Diversen\GPT;

use \Diversen\GPT\Base;

class Shell extends Base
{

    public function __construct()
    {
        parent::__construct();
        $this->endpoint = 'https://api.openai.com/v1/completions';
    }

    public function getCommand()
    {
        return [
            'usage' => 'Command to generate shell commands',
            'options' => [
                ...$this->baseOptions,
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
        $prompt = $parse_argv->getArgument(0);
        $params['prompt'] = $prompt . '. Provide only shell code as output.';
        $text = $this->getCompletions($params);

        if ($parse_argv->getOption('execute')) {
            if ($this->utils->readlineConfirm("Execute command: " . $text . " ?")) {
                passthru($text);
            }
        } else {
            echo $text . PHP_EOL;
        }
    }
}
