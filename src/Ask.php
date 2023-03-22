<?php

namespace Diversen\GPT;

use \Diversen\GPT\Base;

class Ask extends Base
{

    public function __construct()
    {
        parent::__construct();
    }

    public function getCommand()
    {
        return [
            'usage' => 'Get a answer to any question (most likely).',
            'options' => $this->base_options,
            'arguments' => [
                'Prompt' => 'Ask any question.',
            ]
        ];
    }

    public function runCommand(\Diversen\ParseArgv $parse_argv)
    {

        $params = $this->getBaseParams($parse_argv);
        $prompt = $this->getPromptArgument($parse_argv);
        $params['prompt'] = $prompt;
        $params['model'] = 'text-davinci-003';

        $result = $this->getCompletions($params);
        $text = $result->content;

        print($text . PHP_EOL);
        
        if ($result->isError()) {
            return 1;
        }
    }
}
