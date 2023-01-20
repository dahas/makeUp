<?php

use makeUp\src\Cookie;
use makeUp\src\Module;
use makeUp\src\Request;


class TopModels extends Module {

    protected function build(Request $request) : string
    {
        $SampleData = Module::create(modName: "SampleData", useDataMod: true);
        $m["[[SAMPLE_DATA]]"] = $SampleData->build($request);

        if(is_null(Cookie::get("collapseOne_expanded")) || Cookie::get("collapseOne_expanded")) {
            $m["[[HANDLE]]"] = "";
            $m["[[HELP_TEXT]]"] = "show";
        } else {
            $m["[[HANDLE]]"] = "collapsed";
            $m["[[HELP_TEXT]]"] = "";
        }

        $html = $this->getTemplate("TopModels.html")->parse($m);
        return $this->render($html);
    }

}
