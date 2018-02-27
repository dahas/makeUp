<?php

use makeup\lib\Module;
use makeup\lib\Config;


class Index extends Module
{
    public function __construct()
    {
        parent::__construct();
    }


    public function build() : string
    {
        $marker["%MAIN_TITLE%"] = Config::get("page_settings|title");

        return $this->getTemplate()->parse($marker);
    }
}
