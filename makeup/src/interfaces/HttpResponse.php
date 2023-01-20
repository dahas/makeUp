<?php

namespace makeUp\src\interfaces;


interface HttpResponse {

    public function setStatus(string $status): void;
    public function addHeader(string $name, string $value): void;
    public function write(string $data): void;
    public function flush(): void;
}