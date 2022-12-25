<?php

use makeUp\lib\Lang;
use makeup\lib\Module;


class Skincare extends Module {

    protected function build(): string
    {
        $m["[[MODULE]]"] = $this->modName;

        $m["[[MOD_CREATED_SUCCESS]]"] = Lang::get("module_created_success");
        $m["[[CONTINUE_LEARNING]]"] = Lang::get("continue_learning");

        $SampleDataMod = Module::create("SampleData");
        $SampleDataMod->setRender("html");
        $SampleDataMod->setDataMod("SampleData");
        if ($SampleDataMod->isProtected())
            $this->setHistCaching(false);
        $m["[[TEST_MOD]]"] = $SampleDataMod->build();

        $html = $this->getTemplate("Skincare.html")->parse($m);
        return $this->render($html);
    }


    public function doSomething()
    {
        // Do something ...
        return;
    }

}