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
            'options' => $this->base_options,
        ];
    }

    private function writeToFile($content)
    {
        $file = $this->utils->readSingleline('File: ');
        file_put_contents($file, $content, FILE_APPEND);
    }

    private function save(array $params)
    {
        $dialog = '';
        foreach ($params['messages'] as $message) {
            $role = $message['role'];
            $content = $message['content'];
            $dialog .=  ucfirst($role) . ': ' . $content . PHP_EOL . PHP_EOL;
        }
        $this->writeToFile($dialog);
    }

    public function runCommand(\Diversen\ParseArgv $parse_argv)
    {

        $params = $this->getBaseParams($parse_argv);
        $params['model'] = 'gpt-3.5-turbo';

        $params['messages'] = [];
        print("Type 'exit' to exit. 'save' to save" . PHP_EOL);
        while (true) {

            $message = $this->utils->readSingleline('You: ');

            if ($message === 'exit') {
                break;
            }

            if ($message === 'save') {
                $this->save($params);
                exit(0);
            }

            $params['messages'][] = [
                'role' => 'user', 'content' => $message,
            ];

            $result = $this->getChatCompletions($params);
            if ($result->isError()) {
                print($result->content) . PHP_EOL;
                exit(1);

            }

            $content = $result->content;
            $tokens = $result->tokens_used;

            $params['messages'][] = [
                'role' => 'assistant', 'content' => $content,
            ];

            print(PHP_EOL);
            print("Assistant: " . $content . $this->getTokensUsedLine($tokens));
            print(PHP_EOL . PHP_EOL);
        }

        exit(0);
    }
}
