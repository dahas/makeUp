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

        if (Module::checkLogin()) {
            $s["{{TOP_SECRET}}"] = $this->getTemplate()->getSlice("{{TOP_SECRET}}")->parse();
        } else {
            $s["{{TOP_SECRET}}"] = "";
        }

        $html = $this->getTemplate()->parse($m, $s);
        return $this->render($html);
    }
}
