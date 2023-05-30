<?php

namespace Diversen\GPT;

use Diversen\GPT\Base;
use Diversen\ParseArgv;
use Throwable;

class Export extends Base {

    public function getCommand()
    {
        return [
            'usage' => 'Export everything as markdown to a single directory',
            'arguments' => [
                'Dir' => 'The directory to export to.',
            ]
        ];
    }

    public function export(string $dir) {

        // Read all files from data dir
        $files = scandir($this->data_dir);
        foreach ($files as $file) {

            // Skip . and ..
            if (in_array($file, ['.', '..']) ) {
                continue;
            }

            // Skip directories
            if (is_dir($this->data_dir . "/$file")) {
                continue;
            }

            $path = $this->data_dir . '/' . $file;
            $content = file_get_contents($path);
            $json = json_decode($content, true);
            
            $text_str = $this->getDialogAsTxt($json);
            $filename = basename($path);
        
            // Remove file ending .json
            $filename = substr($filename, 0, -5);
            file_put_contents($dir . '/' . $filename . '.md', $text_str);
        }
    }

    public function runCommand(ParseArgv $args)
    {
        $dir = $args->getArgument(0);
        if (!$dir) {
            $dir = $this->utils->readSingleline('Directory: ');
        }

        if (!is_dir($dir)) {
            try {
                mkdir($dir, 0755, true);
            } catch (Throwable $e) {
                print("Could not create dir: $dir" . PHP_EOL);
                return 1;
            }
        }

        $this->export($dir);
        return 0;
    }
}