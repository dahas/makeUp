<?php

namespace makeUp\lib\exceptions;

use Exception;

class FileNotFoundException extends Exception {

    protected $details;

    public function __construct($details)
    {
        $this->details = $details;
        parent::__construct();
    }

    public function __toString()
    {
        return 'File Not Found Exception "' . $this->details . '": ';
    }
}