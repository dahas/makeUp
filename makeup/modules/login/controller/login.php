<?php

use makeup\lib\Module;
use makeup\lib\RQ;
use makeup\lib\Config;
use makeup\lib\Tools;
use makeup\lib\Template;


/**
 * This is a system module
 */
class Login extends Module
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function build() : string
    {
        $m = [];

        return $this->getTemplate()->parse($m);
    }

}
