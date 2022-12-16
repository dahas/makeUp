<?php

use makeUp\lib\Lang;
use makeup\lib\Module;


class Skincare extends Module
{
    public function __construct()
    {
        parent::__construct();
    }


    protected function build() : string
    {
        $m["[[MODULE]]"] = $this->modName;

        $m["[[MOD_CREATED_SUCCESS]]"] = Lang::get("module_created_success");
        $m["[[CONTINUE_LEARNING]]"] = Lang::get("continue_learning");

        $m["[[TEST_MOD]]"] = Module::create("test", "html")->build();

        $html = $this->getTemplate("skincare.html")->parse($m);
        return $this->render($html);
    }


	public function doSomething()
	{ 
        // Do something ...
        return;
	}

}
