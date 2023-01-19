<?php

use makeUp\lib\Lang;
use makeUp\lib\Module;
use makeUp\lib\Request;


class Home extends Module {

    protected function build(Request $request): string
    {
        $parameters = $request->getParameters();

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