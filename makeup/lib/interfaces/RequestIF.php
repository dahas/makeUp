<?php

namespace makeUp\lib\interfaces;


interface RequestIF {

    /**
     * Map this function to GET and POST vars array to apply sanitizing of malicious code.
     * @param mixed $input
     * @return string
     */
    public static function filterInput(mixed $input): string;
}