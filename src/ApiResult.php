<?php

namespace Diversen\GPT;

class ApiResult
{

    public string $tokens_used = '0';
    public string $content = '';
    public array $result;
    public int $error_code = 0;

    public function setResult(string $json)
    {
        $this->result = json_decode($json, true);
    }

    public function setCompletions()
    {
        $this->tokens_used = $this->result["usage"]["total_tokens"] ?? '0';
        $this->content = trim($this->result["choices"][0]["text"]);
    }

    public function setChatCompletions()
    {
        $this->tokens_used = $this->result["usage"]["total_tokens"];
        $this->content = trim($this->result["choices"][0]["message"]["content"]);
    }

    public function isError(): bool
    {
        if ($this->error_code >= 400) {
            return true;
        }
        return false;
    }
}
