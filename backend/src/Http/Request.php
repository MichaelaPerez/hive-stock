<?php

namespace HiveStock\Http;

class Request
{
    private ?array $json = null;

    public function __construct(
        public readonly string $method,
        public readonly string $path,
        public readonly array $query,
    ) {
    }

    public static function capture(): self
    {
        $uriPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

        return new self(
            strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET'),
            rtrim($uriPath, '/') ?: '/',
            $_GET
        );
    }

    public function json(): array
    {
        if ($this->json !== null) {
            return $this->json;
        }

        $raw = file_get_contents('php://input');
        if ($raw === false || trim($raw) === '') {
            return $this->json = [];
        }

        $decoded = json_decode($raw, true);

        return $this->json = is_array($decoded) ? $decoded : [];
    }
}
?>