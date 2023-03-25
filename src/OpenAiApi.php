<?php

namespace Diversen\GPT;

use Diversen\GPT\ApiResult;
use Exception;
use Throwable;

class OpenAiApi
{

    private string $base_path = 'https://api.openai.com/v1';
    private string $api_key = '';
    private int $timeout = 120;
    private int $stream_sleep = 100000;

    /**
     * @param string $api_key
     * @param int $timeout request timeout in seconds
     * @param int $stream_sleep sleep time in microseconds between stream reads
     */
    public function __construct(string $api_key, int $timeout = 120, float $stream_sleep = 0.1)
    {
        $this->api_key = $api_key;
        $this->timeout = $timeout;
        $this->stream_sleep = $stream_sleep * 1000000;
        $this->stream_sleep = (int) $this->stream_sleep;
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
            $endpoint = $this->base_path . '/completions';
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
            $endpoint = $this->base_path . '/chat/completions';
            $result = $this->openAiRequest($endpoint, $params);

            $api_result->setResult($result);
            $api_result->setChatCompletions();
        } catch (Throwable $e) {
            $api_result->error_code = $e->getCode();
            $api_result->content = $e->getMessage();
        }

        return $api_result;
    }

    public function getChatCompletionsStream(array $params): ApiResult
    {
        $result = new ApiResult();

        $tokens = Tokens::estimate(json_encode($params['messages']), 'max');
        $complete_response = '';

        try {
            $endpoint = $this->base_path . '/chat/completions';
            $this->openAiStream($endpoint, $params, function ($content) use (&$complete_response) {
                $complete_response .= $content;
                echo $content;
            });
        } catch (Throwable $e) {
            $result->content = $e->getMessage();
            $result->error_code = $e->getCode();
            return $result;
        }

        $assistant = ['role' => 'assistant', 'text' => $complete_response];
        $json_assistant = json_encode($assistant, true);
        $tokens += Tokens::estimate($json_assistant, 'max');

        $result->content = $complete_response;
        $result->tokens_used = $tokens;

        return $result;
    }

    private function openAiStream(string $endpoint, array $params, callable $callback)
    {

        

        $params['stream'] = true;

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
            $json_str = explode('data: ', $line)[1] ?? '';
            $message = explode('data: ', $line)[0] ?? '';

            if (strpos($message, '[DONE]') === 0) {
                fclose($stream);
                break;
            }

            if (empty($json_str)) {
                continue;
            }

            $json = json_decode($json_str, true);
            $content = $json['choices'][0]['delta']['content'] ?? '';

            $callback($content);
            $finish_reason = $json['finish_reason'] ?? '';
            if ($finish_reason) {
                fclose($stream);
                break;
            }
            usleep($this->stream_sleep);
        }
    }
}
