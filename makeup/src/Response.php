<?php declare(strict_types=1);

namespace makeUp\src;

use makeUp\src\interfaces\HttpResponse;


class Response implements HttpResponse {

    private string $status = "200 OK";
    private array $headers = [];
    private string $body = "";

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function addHeader(string $name, string $value): void
    {
        $this->headers[$name] = $value;
    }

    public function write(string $data): void
    {
        $this->body .= $data;
    }

    public function flush(): void
    {
        header("HTTP/2.0 {$this->status}");
        foreach ($this->headers as $name => $value) {
            header("X-makeUp-{$name}: {$value}");
        }
        print $this->body;
        $this->headers = [];
        $this->body = "";
    }
}