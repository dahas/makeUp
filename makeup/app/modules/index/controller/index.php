<?php

use makeup\lib\Module;
use makeup\lib\Config;


/**
 * The name of a modules class must always be UpperCamelCase!
 * But when you create a module, you must use the name of the
 * class file (without the extension ".php").
 *
 * Class Index
 */
class Index extends Module
{
    /**
     * Calling the parent constructor is required!
     */
    public function __construct()
    {
        parent::__construct();
    }


    /**
     * @return mixed|string
     */
    public function build()
    {
        $marker["%MAIN_TITLE%"] = Config::get("page_settings|title");

        return $this->getTemplate()->parse($marker);
    }
}
