<?php

use makeUp\lib\Lang;
use makeUp\lib\Module;


class Index extends Module
{
    public function __construct()
    {
        parent::__construct();
    }


    public function build() : string
    {
        $m["[[APP_CREATED_SUCCESS]]"] = Lang::get("app_created_success");
        $html = $this->getTemplate()->parse($m);
        return $this->render($html);
    }
}
