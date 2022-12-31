<?php

use makeUp\lib\Module;


class TopModels extends Module {

    protected function build() : string
    {
        $SampleData = Module::create(modName: "SampleData", useDataMod: true);
        $m["[[SAMPLE_DATA]]"] = $SampleData->build();

        $html = $this->getTemplate("TopModels.html")->parse($m);
        return $this->render($html);
    }

}
