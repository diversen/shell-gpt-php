<?php

namespace Diversen\GPT;

use \Diversen\GPT\Base;

class Key extends Base
{

    public function __construct()
    {
        parent::__construct();
    }

    public function getCommand()
    {
        return [
            'usage' => 'Set API key',
        ];
    }

    private function setApiKey()
    {

        $file = $this->base_dir . '/api_key.txt';
        $api_key = $this->utils->readSingleline("Please enter a valid API key: ");
        $res = file_put_contents($file, $api_key);
        if ($res === false) {
            print("Could not write API key to file" . PHP_EOL);
            return 1;
        }

        $params = $this->default_options;
        $params['prompt'] = "Say 'API key seems to work!' and nothing more. Just a test";
        $result = $this->getCompletions($params);

        if ($result->isError()) {
            $text = $result->content;
            print($text . PHP_EOL);
            return 1;
        }

        print ($result->content) . PHP_EOL;
    }

    public function runCommand(\Diversen\ParseArgv $parse_argv)
    {
        $this->setApiKey();
    }
}
