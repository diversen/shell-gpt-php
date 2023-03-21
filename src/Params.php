<?php

namespace Diversen\GPT;

use \Diversen\GPT\Base;

class Params extends Base
{

    public function __construct()
    {
        parent::__construct();
    }

    public function getCommand()
    {
        return [
            'usage' => 'Command to save params to a config file',
            'options' => [
                '--show' => 'Show the current parameters',
                '--set' => 'Set default parameters',
                '--delete' => 'Reset parameters to default values',
            ]
        ];
    }

    private function showParams()
    {

        if (!file_exists($this->params_file)) {
            echo "No config file found. Create one with the command: shell-gpt params --set" . PHP_EOL;
            exit(1);
        }

        $json_params = file_get_contents($this->params_file);
        echo $json_params . PHP_EOL;
    }

    private function setParams()
    {

        $params = [];
        $options = $this->getDefaultOptions();
        unset($options['n'], $options['stream']);

        foreach ($options as $key => $option) {
            $description = $this->base_options['--' . $key];
            print($description . PHP_EOL);

            $value = $this->utils->readSingleline("Set $key [current: $option] to: ");
            if (!$value) {
                $params[$key] = $this->default_options[$key];
            } else {
                $params[$key] = $value;
            }
        }

        foreach ($params as $key => $value) {
            $params[$key] = $this->castOptions($key, $value);
        }

        $json = json_encode($params, JSON_PRETTY_PRINT);
        file_put_contents($this->params_file, $json);
    }

    private function deleteParams()
    {
        if (file_exists($this->params_file)) {
            unlink($this->params_file);
        }
    }

    public function runCommand(\Diversen\ParseArgv $parse_argv)
    {

        if ($parse_argv->getOption('show')) {
            $this->showParams();
            exit(0);
        }

        if ($parse_argv->getOption('set')) {
            $this->setParams();
            exit(0);
        }

        if ($parse_argv->getOption('delete')) {
            $this->deleteParams();
            exit(0);
        }

        $this->showParams();
    }
}
