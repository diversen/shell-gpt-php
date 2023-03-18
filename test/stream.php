<?php

require_once "vendor/autoload.php";

use Diversen\GPT\Base;
use Diversen\GPT\OpenAiApi;


$base = new Base();
$api_key = $base->getApiKey();

$openai_api = new OpenAiApi($api_key);

$params['stream'] = true;
$params['model'] = 'gpt-3.5-turbo';
$params['messages'] = [];
$params['messages'][] = [
    'role' => 'user', 'content' => "Say hello world and nothing more.",
];

$headers[] = 'Content-Type: application/json';
$headers[] = 'Authorization: Bearer ' . $api_key;
$headers[] = 'Accept: text/event-stream';

// $endpoint = 'https://api.openai.com/v1/chat/completions';
// $openai_api->openAiStream($endpoint, $params, function ($content) {
//     echo $content;
// });

$base->getChatCompletionsStream($params, function ($content) {
    echo $content;
});


$params = array (
    'model' => 'gpt-3.5-turbo',
    'max_tokens' => 2048,
    'temperature' => 1,
    'n' => 1,
    'stream' => true,
    'messages' => 
    array (
      0 => 
      array (
        'role' => 'user',
        'content' => 'Hello world!!!!!',
      ),
    ),
);
