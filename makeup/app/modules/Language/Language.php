<?php

use makeUp\lib\Template;
use makeUp\src\Module;
use makeUp\src\Request;
use makeUp\src\Utils;
use makeUp\src\Cookie;
use makeUp\src\Session;


class Language extends Module {

    protected function build(Request $request): string
    {
        $m = [];
        $s = [];

        $template = Template::load("Language");

        $suppLangs = Utils::getSupportedLanguages();
        $current = Cookie::get("lang_code") ?? Utils::getUserLanguageCode();

        $m["[[CURRENT_LANGUAGE]]"] = $suppLangs[$current];

        $slice = $template->getSlice("{{SUPPORTED_LANGUAGES}}");

        $s["{{SUPPORTED_LANGUAGES}}"] = "";

        foreach ($suppLangs as $code => $name) {
            $sm = [];
            $sm["[[ACTIVE]]"] = $code == $current ? "active" : "";
            $sm["[[CC]]"] = $code;
            $sm["[[LANG_NAME]]"] = $name;
            $s["{{SUPPORTED_LANGUAGES}}"] .= $slice->parse($sm);
        }

        return $template->parse($m, $s);
    }


    public function change(Request $request): string
    {
        Cookie::set("lang_code", $request->getParameter("cc"));
        Session::clear("translation"); // String resources must be renewed in the session
        return json_encode(['result' => 1]);
    }

}