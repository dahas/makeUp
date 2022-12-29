<?php

use makeUp\lib\Lang;
use makeUp\lib\Module;


class Test extends Module {

    protected function build() : string
    {
        $params = Module::getParameters(); // Use this method to retrieve sanitized GET and POST data.

        $m["[[MODULE]]"] = $this->modName;

        $m["[[MOD_CREATED_SUCCESS]]"] = Lang::get("module_created_success");
        $m["[[CONTINUE_LEARNING]]"] = Lang::get("continue_learning");

        $html = $this->getTemplate("Test.html")->parse($m);
        return $this->render($html);
    }

    protected function doit() {
        return "DID IT!";
    }

}
