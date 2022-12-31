<?php

use makeUp\lib\Lang;
use makeup\lib\Module;


class Skincare extends Module {

    protected function build(): string
    {
        $m["[[MODULE]]"] = $this->getDataMod();
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