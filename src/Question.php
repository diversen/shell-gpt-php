<?php

namespace Diversen\GPT;

use \Diversen\GPT\Base;

class Question extends Base
{

    public function __construct()
    {
        parent::__construct();
    }

    public function getCommand()
    {
        return [
            'usage' => 'Get an answer to any question (most likely).',
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
        if ($prompt === false) {
            print("No prompt given. Please specify your prompt. " . PHP_EOL);
            return 10;
        }
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
