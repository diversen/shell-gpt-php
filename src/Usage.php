<?php

namespace Diversen\GPT;

use \Diversen\GPT\Base;

class Usage extends Base
{
    public function getTokensUsed()
    {
        $file = $this->base_dir . '/tokens_used.txt';
        if (!file_exists($file)) {
            return 0;
        }
        $content = trim(file_get_contents($file));
        $lines = explode(PHP_EOL, $content);

        $tokens_all = [];
        $tokens_all['Last 24 hours'] = 0;
        $tokens_all['Last 7 days'] = 0;
        $tokens_all['Last 30 days'] = 0;
        $tokens_all['Last 90 days'] = 0;

        $last_24_hours = time() - (24 * 60 * 60);
        $last_7_days = time() - (7 * 24 * 60 * 60);
        $last_30_days = time() - (30 * 24 * 60 * 60);
        $last_90_days = time() - (90 * 24 * 60 * 60);

        foreach ($lines as $line) {
            if (!$line) {
                continue;
            }
            $parts = explode(',', $line);
            $tokens = (int) $parts[1];
            $unix_time = (int) $parts[0];

            if ($unix_time > $last_24_hours) {
                $tokens_all['Last 24 hours'] += $tokens;
            }
            if ($unix_time > $last_7_days) {
                $tokens_all['Last 7 days'] += $tokens;
            }
            if ($unix_time > $last_30_days) {
                $tokens_all['Last 30 days'] += $tokens;
            }
            if ($unix_time > $last_90_days) {
                $tokens_all['Last 90 days'] += $tokens;
            }
        }
        return $tokens_all;
    }

    public function __construct()
    {
        parent::__construct();
    }

    public function getCommand()
    {
        return [
            'usage' => 'Show usage of tokens',
            'options' => [],
            'arguments' => []
        ];
    }

    public function runCommand(\Diversen\ParseArgv $parse_argv)
    {

        $usage = $this->getTokensUsed();
        if (!$usage) {
            echo '0 usage of tokens' . PHP_EOL;
            return 0;
        }
        foreach ($usage as $key => $value) {
            $key = str_pad($key . ": ", 15, ' ', STR_PAD_RIGHT);
            echo $key . $value . PHP_EOL;
        }

        return 0;
    }
}
