<?php

namespace makeUp\lib\interfaces;


interface RequestIF {

    public function isXHR(): bool;
    public function getMethod(): string;
    public function issetRouteHeader(): bool;
    public function getRouteHeader(): string;
    public function getModule(): string;
    public function getTask(): string;
    public function getParameters(): array;
    public function getParameter(string $name): string;
    public function parseRequest(array $request): array;
    public static function filterInput(mixed $input): string;
}