<?php declare(strict_types = 1);

namespace makeUp\lib;
use makeUp\lib\interfaces\RequestIF;


class Request implements RequestIF
{
    /**
     * Sanitizing GET vars array.
     * @param array $query
     * @return array
     */
    public static function parseQuery(array $query): array
    {
        return array_map('self::filterInput', $query);
    }


    /**
     * Sanitizing POST vars array.
     * @param array $formData
     * @return array
     */
    public static function parseFormData(array $formData): array
    {
        return array_map('self::filterInput', $formData);
    }


    public static function filterInput(mixed $input): string
    {
        return htmlspecialchars(string: $input, encoding: Config::get("metatags", "charset"));
    }
}