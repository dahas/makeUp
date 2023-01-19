<?php

use makeUp\lib\Lang;
use makeup\lib\Module;
use makeUp\lib\Request;


class Skincare extends Module {

    protected function build(Request $request): string
    {
        $m["[[MODULE]]"] = $this->modName;
        $m["[[MOD_CREATED_SUCCESS]]"] = Lang::get("module_created_success");
        $m["[[CONTINUE_LEARNING]]"] = Lang::get("continue_learning");

        $html = $this->getTemplate("Skincare.html")->parse($m);
        return $this->render($html);
    }


    public function doSomething()
    {
        // Do something ...
        return;
    }

}