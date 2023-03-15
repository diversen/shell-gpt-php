<?php

namespace Diversen\GPT;

use Diversen\GPT\ApiResult;
use Exception;
use Throwable;

class OpenAiApi
{

    private string $api_key = '';
    private $timeout = 0;

    public function __construct($api_key, $timeout = 0)
    {
        $this->api_key = $api_key;
        $this->timeout = $timeout;
    }

    private function openAiRequest($endpoint, $params)
    {

        // Generate $params by merging default options with $params
        // $params = array_merge($default_params, $params);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);

        $headers = array();
        $headers[] = 'Content-Type: application/json';
        $headers[] = 'Authorization: Bearer ' . $this->api_key;

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $mh = curl_multi_init();
        curl_multi_add_handle($mh, $ch);

        do {
            $status = curl_multi_exec($mh, $active);
        } while ($status === CURLM_CALL_MULTI_PERFORM || $active);

        while ($active && $status === CURLM_OK) {
            if (curl_multi_select($mh) === -1) {
                usleep(100);
            }
            do {
                $status = curl_multi_exec($mh, $active);
            } while ($status === CURLM_CALL_MULTI_PERFORM);
        }

        if ($status !== CURLM_OK) {
            throw new Exception(501, curl_multi_strerror($status));
        }

        $http_code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $result = curl_multi_getcontent($ch);

        if ($result === '') {
            throw new Exception("No internet connection", 500);
        }

        if ($http_code >= 400) {
            $error_message = $this->getAPIError($result);
            throw new Exception($error_message, $http_code);
        }

        curl_multi_remove_handle($mh, $ch);
        curl_multi_close($mh);
        curl_close($ch);

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
        // $endpoint = 'https://api.openai.com/v1/chat/completions';
        // $api_result = $this->openAiRequest($endpoint, $params);

        // $result = new ApiResult($api_result);
        // $result->setChatCompletions();

        // return $result;
    }

    /**
     * Stream completions. Not working yet 
     */
    public function openAiRequestStream($endpoint, $params)
    {
        $params["stream"] = true;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        curl_setopt($ch, CURLOPT_TIMEOUT, 0);
    
        $headers = array();
        $headers[] = 'Content-Type: application/json';
        $headers[] = 'Authorization: Bearer ' . $this->api_key;
        $headers[] = 'Accept: text/event-stream';
    
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
        $mh = curl_multi_init();
        curl_multi_add_handle($mh, $ch);
    
        $result = '';
        do {
            curl_multi_select($mh);
            curl_multi_exec($mh, $active);
    
            $info = curl_multi_info_read($mh);
            if ($info && $info['result'] == CURLM_OK) {
                $ch = $info['handle'];
                $data = curl_multi_getcontent($ch);
                $result .= $data;
                if (strpos($result, '[DONE]') !== false) {
                    break;
                }
            }
        } while ($active || $result === '');
    
        if (curl_errno($ch)) {
            throw new Exception(curl_error($ch));
        }
    
        curl_multi_remove_handle($mh, $ch);
        curl_multi_close($mh);
        curl_close($ch);
    
        return $result;
    }
}
