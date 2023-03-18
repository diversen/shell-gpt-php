<?php

namespace Diversen\GPT;

use Diversen\GPT\ApiResult;
use Error;
use Exception;
use JsonException;
use Throwable;

class OpenAiApi
{

    private string $api_key = '';
    private $timeout = 120;

    public function __construct($api_key, $timeout = 120)
    {
        $this->api_key = $api_key;
        $this->timeout = $timeout;
    }

    private function parseHeaders($headers)
    {
        $head = array();
        foreach ($headers as $k => $v) {
            $t = explode(':', $v, 2);
            if (isset($t[1]))
                $head[trim($t[0])] = trim($t[1]);
            else {
                $head[] = $v;
                if (preg_match("#HTTP/[0-9\.]+\s+([0-9]+)#", $v, $out))
                    $head['response_code'] = intval($out[1]);
            }
        }
        return $head;
    }

    private function openAiRequest($endpoint, $params)
    {

        $headers = array();
        $headers[] = 'Content-Type: application/json';
        $headers[] = 'Authorization: Bearer ' . $this->api_key;

        $options = array(
            'http' => array(
                'header'  => $headers,
                'method'  => 'POST',
                'content' => json_encode($params),
                'timeout' => $this->timeout,
            ),
        );

        $context  = stream_context_create($options);

        try {
            $stream = fopen($endpoint, 'r', false, $context);
        } catch (Throwable $e) {
            throw new Exception("Could not read from API endpoint.", 500);
        }

        $result = stream_get_contents($stream);
        $headers = $this->parseHeaders($http_response_header);
        if ($headers['response_code'] >= 400) {
            $error_message = $this->getAPIError($result);
            throw new Exception($error_message, $headers['response_code']);
        }

        return $result;
    }

    private function getAPIError($result)
    {
        $result = json_decode($result, true);
        return "API ERROR: " . $result["error"]["message"];
    }

    public function getCompletions(array $params): ApiResult
    {
        $api_result = new ApiResult();

        try {
            $endpoint = 'https://api.openai.com/v1/completions';
            $result = $this->openAiRequest($endpoint, $params);

            $api_result->setResult($result);
            $api_result->setCompletions();
        } catch (Throwable $e) {

            $api_result->error_code = $e->getCode();
            $api_result->content = $e->getMessage();
        }

        return $api_result;
    }

    public function getChatCompletions(array $params): ApiResult
    {
        $api_result = new ApiResult();

        try {
            $endpoint = 'https://api.openai.com/v1/chat/completions';
            $result = $this->openAiRequest($endpoint, $params);

            $api_result->setResult($result);
            $api_result->setChatCompletions();
        } catch (Throwable $e) {

            $api_result->error_code = $e->getCode();
            $api_result->content = $e->getMessage();
        }

        return $api_result;
    }

    public function openAiStream(string $endpoint, array $params, callable $callback)
    {
        $headers = array();
        $headers[] = 'Content-Type: application/json';
        $headers[] = 'Authorization: Bearer ' . $this->api_key;
        $headers[] = 'Accept: text/event-stream';

        $options = array(
            'http' => array(
                'header'  => $headers,
                'method'  => 'POST',
                'content' => json_encode($params),
                'timeout' => $this->timeout,
            ),
        );

        $context  = stream_context_create($options);

        try {
            $stream = fopen($endpoint, 'r', false, $context);
        } catch (Throwable $e) {
            throw new Exception("Could not read from API endpoint.", 500);
        }

        while (!feof($stream)) {
            
            $line = fgets($stream);
            $line = explode('data: ', $line)[1] ?? '';
            if (empty($line)) {
                continue;
            }

            $json = json_decode($line, true);

            $content = $json['choices'][0]['delta']['content'] ?? '';
            $callback($content);
            $finish_reason = $json['finish_reason'] ?? '';
            if ($finish_reason) {
                break;
            }
            usleep(100000);
        }
    }
}
