<?php declare(strict_types=1);

namespace makeUp\lib\traits;


trait Utils {

    /**
     * German formatting of a float number. 
     * For example: 23400.5 gets 23.400,50
     */
    public function formatNumber(int|float $input): string
    {
        return number_format($input, 2, ',', '.');
    }

    /**
     * Sanitizing GET variables.
     * @param array $query
     * @return array
     */
    public function parseQuery(array $query): array
    {
        return array_map('self::filterInput', $query);
    }

    /**
     * Sanitizing POST variables.
     * @param array $formData
     * @return array
     */
    public function parseFormData(array $formData): array
    {
        return array_map('self::filterInput', $formData);
    }


    /**
     * Applies Filter.
     * @param mixed $input
     * @return string
     */
    private static function filterInput($input): string
    {
        return filter_var($input, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    }
}