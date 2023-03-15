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
            'options' => $this->base_options,
            'arguments' => [
                'Prompt' => 'The prompt to generate completions for.',
            ]
        ];
    }

    public function runCommand(\Diversen\ParseArgv $parse_argv)
    {

        $params = $this->getBaseParams($parse_argv);
        $prompt = $this->getPromptArgument($parse_argv);

        $params['prompt'] = $prompt;
        $result = $this->getCompletions($params);
        $text = $result->content;
        print($text . PHP_EOL);

        if ($result->isError()) {
            exit(1);
        }
    }
}
