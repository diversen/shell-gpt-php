<?php

namespace Diversen\GPT;

use \Diversen\GPT\Base;

class Dialog extends Base
{

    public function __construct()
    {
        parent::__construct();
    }

    public function getCommand()
    {
        return [
            'usage' => 'Start a dialog.',
            'options' => $this->baseOptions,
        ];
    }

    public function runCommand(\Diversen\ParseArgv $parse_argv)
    {

        $params = $this->getBaseParams($parse_argv);
        $params['model'] = 'gpt-3.5-turbo';
        
        $params['messages'] = [];
        print("Type 'exit' to exit" . PHP_EOL);
        while(true) {
            $message = $this->utils->readSingleline('You: ');
            if ($message === 'exit') {
                break;
            }

            $params['messages'][] = [
                'role' => 'user', 'content' => $message,
            ];
            
            $result = $this->getChatCompletions($params);
            $content = $result->content;
            $tokens = $result->tokens_used;
            $params['messages'][] = [
                'role' => 'assistant', 'content' => $content,
            ];
            print("Assistant: " . $content . $this->getTokensUsedLine($tokens) . PHP_EOL);
        }
    }
}
