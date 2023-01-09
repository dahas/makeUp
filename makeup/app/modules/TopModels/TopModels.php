<?php

use makeUp\lib\Cookie;
use makeUp\lib\Module;


class TopModels extends Module {

    protected function build() : string
    {
        $SampleData = Module::create(modName: "SampleData", useDataMod: true);
        $m["[[SAMPLE_DATA]]"] = $SampleData->build();

        if(Cookie::get("collapseOne_expanded")) {
            $m["[[COLLAPSED]]"] = "";
            $m["[[SHOW]]"] = "show";
        } else {
            $m["[[COLLAPSED]]"] = "collapsed";
            $m["[[SHOW]]"] = "";
        }

        $html = $this->getTemplate("TopModels.html")->parse($m);
        return $this->render($html);
    }

}
