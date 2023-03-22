<?php

namespace Diversen\GPT;

use \Diversen\GPT\Base;

class Code extends Base
{

    public function __construct()
    {
        parent::__construct();
    }

    public function getCommand()
    {
        return [
            'usage' => 'Command to generate code',
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
        if ($prompt === false) {
            print("No prompt given. Please specify your prompt. " . PHP_EOL);
            return 10;
        }

        $prompt .= '. Provide only code as output.';
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
