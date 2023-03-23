<?php

namespace Diversen\GPT;

use Diversen\GPT\Base;
use Throwable;

class Dialog extends Base
{

    private array $commands = [
        'save' => 'Save dialog to file',
        'exec' => 'Execute a command and feed the output to the dialog',
        'exit' => 'Exit the dialog',
        'comm' => 'Show all commands',
    ];
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

    private function exit()
    {
        return 0;
    }

    private function getSaveString(array $params = [])
    {
        $dialog = '';
        foreach ($params['messages'] as $message) {
            $role = $message['role'];
            $content = $message['content'];
            $dialog .=  ucfirst($role) . ': ' . $content . PHP_EOL . PHP_EOL;
        }
        return $dialog;
    }

    private function save(array &$params = [])
    {

        $content = $this->getSaveString($params);
        $file = $this->utils->readSingleline('File: ');
        file_put_contents($file, $content);
        return 0;
    }

    private function exec(array &$params = [])
    {
        $command = $this->utils->readSingleline('Command: ');
        $this->utils->execSilent($command);
        $stderr = $this->utils->getStderr();
        if ($stderr) {
            echo $this->utils->colorOutput($stderr, 'error');
            echo PHP_EOL;
            return 1;
        }

        $stdout = $this->utils->getStdout();
        if ($stdout) {
            echo $this->utils->colorOutput($stdout, 'notice');
            echo PHP_EOL;
            return $stdout;
        }
    }

    private function comm()
    {

        $command_help = 'Available commands: ' . PHP_EOL . PHP_EOL;
        foreach ($this->commands as $command => $help) {
            $command_help.= $this->utils->colorOutput($command, 'notice') . ' - ' . $help . PHP_EOL;
        }
        print($command_help) . PHP_EOL;
        print("Type a message to ChatGPT. Maybe 'hello world!' You may also use above commands. " . PHP_EOL);
        return 1;
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

        $this->comm();
        $command_names = array_keys($this->commands);
        
        while (true) {

            $message = $this->utils->readSingleline('You: ');

            // Check if $message is a command
            if (in_array(trim($message), $command_names)) {

                $command = $message;

                // exit on 0
                // continue on 1
                // if not 0 or 1, then it is a message

                $res = $this->$command($params);
                if ($res === 0) {
                    // save dialog to data dir as json file
                    // Make a good name for a log file
                    // Get yyyy-mm-dd_hh-mm-ss
                    $date = date('Y-m-d_H-i-s');
                    $file = $this->data_dir . '/dialog_' . $date . '.json';
                    try {
                        file_put_contents($file, json_encode($params['messages'], JSON_PRETTY_PRINT));
                        print('Dialog saved to ' . $file . PHP_EOL);
                    } catch (Throwable $e) {
                        print($e->getMessage() . PHP_EOL);
                        return 1;
                    }
                    
                    return 0;
                }

                if ($res === 1) {
                    continue;
                }

                $message = $res;
            }

            $params['messages'][] = [
                'role' => 'user', 'content' => $message,
            ];

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
