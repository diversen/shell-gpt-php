<?php

namespace Diversen\GPT;

class Loader
{

    private $chars = [];
    private $steps = 0;
    private $running_time = 0;

    public function __construct(int $max_dots = 3, $usleep = 10000)
    {

        $this->running_time = microtime(true);
        for ($i = 1; $i <= $max_dots; $i++) {
            $this->chars[] = str_repeat('.', $i);
        }

        $this->chars[] = str_repeat("\x08", $max_dots);
        $this->chars[] = str_repeat(' ', $max_dots);
        $this->chars[] = str_repeat("\x08", $max_dots);
    }

    public function stepForward()
    {

        // If one second has passed, then we reset the steps
        if (microtime(true) - $this->running_time > 0.1) {
            $this->running_time = microtime(true);
            echo $this->chars[$this->steps] . "\r";
            $this->steps++;
            if ($this->steps >= count($this->chars)) {
                $this->steps = 0;
            }
        }
    }
}
