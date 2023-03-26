<?php

# The API implements a couple of endpoints. 
# /completions and /chat/completions
# And both of them can be streamed or not.

require_once "vendor/autoload.php";

use Diversen\GPT\OpenAiApi;

// use Diversen\GPT\Base;
// $base = new Base();
// $api_key = $base->getApiKey();

$api_key = 'sk-***';


#
# /completions (no streaming)
#

$completion_params = array (
    'model' => 'text-davinci-003',
    'max_tokens' => 10,
    'temperature' => 0,
    'n' => 1,
    'stream' => false,
    'prompt' => 'Only say "Hello world!"',
);


$api = new OpenAiApi($api_key);
$result = $api->getCompletions($completion_params);
if ($result->isError()) {
    echo $result->error_message;
}

echo $result->content . PHP_EOL;

#
# /chat/completions (no streaming)
#

$api = new OpenAiApi($api_key);
$chat_completions_params = array (
    'model' => 'gpt-3.5-turbo',
    'max_tokens' => 10,
    'temperature' => 0,
    'n' => 1,
    'stream' => false,
    'messages' => 
    array (
      0 => 
      array (
        'role' => 'user',
        'content' => 'say "Hello world from chat completions" and nothing more',
      ),
    ),
);

$result = $api->getChatCompletions($chat_completions_params);
if ($result->isError()) {
    echo $result->error_message;
}

echo $result->content . PHP_EOL;

#
# /chat/completion (streaming)
# 

$params = array (
    'model' => 'gpt-3.5-turbo',
    'max_tokens' => 2048,
    'temperature' => 0,
    'n' => 1,
    'stream' => true,
    'messages' => 
    array (
      0 => 
      array (
        'role' => 'user',
        'content' => 'say "Hello world!" and nothing more',
      ),
    ),
);

#
# /completions (streaming)
# 

$completion_params['stream'] = true;
$api = new OpenAiApi(api_key: $api_key, stream_sleep: 0.1, timeout: 4);
try {
    $result = $api->openAiStream('/completions', $completion_params, function ($json) {
        $content = $json['choices'][0]['text'] ?? '';
        echo $content;
    });
    echo PHP_EOL;
} catch (Exception $e) {
    echo $e->getMessage();
}

#
# /chat/completions (streaming)
# 

$chat_completions_params['stream'] = true;
$api = new OpenAiApi(api_key: $api_key, stream_sleep: 0.1, timeout: 4);
try {
    $api->openAiStream('/chat/completions', $chat_completions_params, function ($json) {
        $content = $json['choices'][0]['delta']['content'] ?? '';
        echo $content;
    });
    echo PHP_EOL;
} catch (Exception $e) {
    echo $e->getMessage();
}
