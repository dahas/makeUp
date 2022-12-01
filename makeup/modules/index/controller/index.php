<?php

use makeUp\lib\Module;


class Index extends Module
{
    public function __construct()
    {
        parent::__construct();
    }


    public function build() : string
    {
        return $this->getTemplate()->parse();
    }
}
