<?php

namespace Diversen\GPT;

use \Diversen\GPT\Base;

class Prompts extends Base
{

    public function __construct()
    {
        parent::__construct();
    }

    public function getCommand()
    {
        return [
            'usage' => 'Generate a bunch of ready made assistants from https://github.com/f/awesome-chatgpt-prompts',
            'options' => [],
        ];
    }

    public function sanitizeFilename (string $file) {
        
        // Remove all illegal characters from a filename
        $file = preg_replace("/[^a-zA-Z0-9\.\-\_]/", '', $file);
        return $file;

    }

    public function runCommand(\Diversen\ParseArgv $parse_argv)
    {

        
        // Download this url: https://raw.githubusercontent.com/f/awesome-chatgpt-prompts/main/prompts.csv
        // And parse it the csv file into an array
        $file = file_get_contents('https://raw.githubusercontent.com/f/awesome-chatgpt-prompts/main/prompts.csv');
        $lines = explode(PHP_EOL, $file);
        // Remove first line
        array_shift($lines);
        $prompts = [];
        foreach ($lines as $line) {
            $parts = explode(',', $line);
            
            // lowercase the name and replace spaces with dashes
            $parts[0] = str_replace(' ', '_', strtolower($parts[0]));
            
            // Sanitize
            $parts[0] = $this->sanitizeFilename($parts[0]);
            
            $prompts[] = [
                'name' => $parts[0],
                'prompt' => $parts[1],

            ];
        }

        if (!is_dir($this->data_dir . '/prompts')) {
            mkdir($this->data_dir . '/prompts');
        }

        foreach($prompts as $prompt) {
            $file = $this->data_dir . '/prompts/' . $prompt['name'] . '.txt';
            file_put_contents($file, $prompt['prompt']);
        }

        print("Done. You can now run the following commands:" . PHP_EOL);
        print($this->data_dir . '/prompts/');
        
        die;
        foreach($prompts as $prompt) {
            print("gpt " . $prompt['name'] . PHP_EOL);
        }
    }
}
