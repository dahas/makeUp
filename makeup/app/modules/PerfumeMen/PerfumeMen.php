<?php

use makeUp\lib\Template;
use makeUp\src\Lang;
use makeup\src\Module;


class PerfumeMen extends Module
{
    protected function build() : string
    {
        $m["[[MODULE]]"] = $this->modName;

        $m["[[MOD_CREATED_SUCCESS]]"] = Lang::get("module_created_success");
        $m["[[CONTINUE_LEARNING]]"] = Lang::get("continue_learning");

        $html = Template::load("PerfumeMen.html")->parse($m);
        return $this->render($html);
    }


	public function doSomething()
	{ 
        // Do something ...
	}

}
