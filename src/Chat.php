<?php

namespace Diversen\GPT;

use \Diversen\GPT\Base;

class Chat extends Base
{

    public function __construct()
    {
        parent::__construct();
    }

    public function getCommand()
    {
        return [
            'usage' => 'Answer questions',
            'options' => $this->baseOptions,
            'arguments' => [
                'Prompt' => 'The prompt to generate completions for.',
            ]
        ];
    }

    public function runCommand(\Diversen\ParseArgv $parse_argv)
    {

        $params = $this->getBaseParams($parse_argv);
        $prompt = $parse_argv->getArgument(0);
        $params['prompt'] = $prompt;
        $text = $this->getCompletions($params);
        echo $text . PHP_EOL;
    }
}
