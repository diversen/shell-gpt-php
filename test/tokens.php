<?php

require_once "vendor/autoload.php";

use Diversen\GPT\Tokens;

$text = '{"role": "user", "content": "Hello!"}';

echo Tokens::estimate_tokens($text, 'max' ) . PHP_EOL;

$text_2 = "\n\nHello there, how may I assist you today?";

echo Tokens::estimate_tokens($text_2, "average") . PHP_EOL;