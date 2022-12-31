<?php

use makeUp\lib\Lang;
use makeUp\lib\Module;


class TopModels extends Module {

    protected function build() : string
    {
        $params = Module::requestData(); // Use this method to retrieve sanitized GET and POST data.

        $m["[[MODULE]]"] = $this->modName;

        $m["[[MOD_CREATED_SUCCESS]]"] = Lang::get("module_created_success");
        $m["[[CONTINUE_LEARNING]]"] = Lang::get("continue_learning");

        $SampleData = Module::create(modName: "SampleData", useDataMod: true);
        $m["[[SAMPLE_DATA]]"] = $SampleData->build();

        $html = $this->getTemplate("TopModels.html")->parse($m);
        return $this->render($html);
    }

}
