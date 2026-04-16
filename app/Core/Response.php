<?php
declare(strict_types=1);

namespace App\Core;

final class Response
{
    private int $status = 200;
    private array $headers = [];
    private string $body = '';

    public function setStatus(int $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function header(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    public function body(string $body): self
    {
        $this->body = $body;
        return $this;
    }

    public static function html(string $html, int $status = 200): self
    {
        return (new self())->setStatus($status)->header('Content-Type', 'text/html; charset=utf-8')->body($html);
    }

    public static function json(mixed $data, int $status = 200): self
    {
        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return (new self())->setStatus($status)->header('Content-Type', 'application/json; charset=utf-8')->body($json !== false ? $json : '{}');
    }

    public static function redirect(string $location, int $status = 302): self
    {
        return (new self())->setStatus($status)->header('Location', url($location));
    }

    public static function download(string $content, string $filename, string $mime = 'application/octet-stream'): self
    {
        return (new self())
            ->header('Content-Type', $mime)
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Content-Length', (string) strlen($content))
            ->body($content);
    }

    public function send(): void
    {
        if (!headers_sent()) {
            http_response_code($this->status);
            foreach ($this->headers as $name => $value) {
                header("$name: $value");
            }
        }
        echo $this->body;
    }
}
