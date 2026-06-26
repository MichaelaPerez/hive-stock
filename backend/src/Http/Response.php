<?php

namespace HiveStock\Http;

class Response
{
    public function __construct(
        private readonly array $body,
        private readonly int $status = 200,
        private readonly array $headers = [],
    ) {
    }

    public static function json(array $body, int $status = 200): self
    {
        return new self($body, $status);
    }

    public static function jsonError(string $message, int $status = 400, string $code = 'error'): self
    {
        return new self([
            'error' => [
                'message' => $message,
                'code' => $code,
            ],
        ], $status);
    }

    public function send(): void
    {
        http_response_code($this->status);
        header('Content-Type: application/json; charset=utf-8');

        foreach ($this->headers as $name => $value) {
            header($name . ': ' . $value);
        }

        echo json_encode($this->body, JSON_UNESCAPED_SLASHES);
    }
}
?>