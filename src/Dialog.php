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
        file_put_contents($file, $content);
    }

    private function exit()
    {
        return 0;
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
        return 0;
    }

    public function runCommand(\Diversen\ParseArgv $parse_argv)
    {
        $this->runCommandStream($parse_argv);
    }

    public function runCommandStream(\Diversen\ParseArgv $parse_argv)
    {

        $params = $this->getBaseParams($parse_argv);
        $params['model'] = 'gpt-3.5-turbo';
        $params['messages'] = [];
        print("Type '/exit' to exit. '/save' to save to file" . PHP_EOL);
        while (true) {

            $message = $this->utils->readSingleline('You: ');
            $params['messages'][] = [
                'role' => 'user', 'content' => $message,
            ];

            // Check if $message is a command
            if (substr($message, 0, 1) === '/') {
                $command = substr($message, 1);
                $command = explode(' ', $command);
                $command = $command[0];
                if (method_exists($this, $command)) {
                    $res = $this->$command($params);

                    // Only exit if exit returns 0
                    if ($res === 0) {
                        return 0;
                    }
                }
            }

            print(PHP_EOL);
            print("Assistant: ");

            $result = $this->getChatCompletionsStream($params);
            if ($result->isError()) {
                print ($result->content) . PHP_EOL;
                return 1;
            }

            $content = $result->content;
            $tokens = $result->tokens_used;
            print($this->getTokensUsedLine($tokens));
            print(PHP_EOL . PHP_EOL);

            $params['messages'][] = [
                'role' => 'assistant', 'content' => $content,
            ];
        }
    }
}
