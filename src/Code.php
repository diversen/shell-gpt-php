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
            'options' => $this->baseOptions,
            'arguments' => [
                'Prompt' => 'The prompt to generate completions for.',
            ]
        ];
    }

    public function runCommand(\Diversen\ParseArgv $parse_argv)
    {
        
        $params = $this->getBaseParams($parse_argv);
        $prompt = $this->getPromptArgument($parse_argv); 
        $params['prompt'] = $prompt . '. Provide only code as output.';
        
        $result = $this->getCompletions($params);
        $text = $result->content;
        
        print($text . PHP_EOL);
        
    }
}
