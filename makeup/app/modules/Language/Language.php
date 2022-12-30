<?php

use makeUp\lib\Module;
use makeUp\lib\Utils;
use makeUp\lib\Cookie;
use makeUp\lib\Session;


class Language extends Module {

    protected function build(): string
    {
        $m = [];
        $s = [];

        $suppLangs = Utils::getSupportedLanguages();
        $current = Cookie::get("lang_code") ?? Utils::getUserLanguageCode();

        $m["[[CURRENT_LANGUAGE]]"] = $suppLangs[$current];

        $slice = $this->getTemplate()->getSlice("{{SUPPORTED_LANGUAGES}}");

        $s["{{SUPPORTED_LANGUAGES}}"] = "";

        foreach ($suppLangs as $code => $name) {
            $sm = [];
            $sm["[[ACTIVE]]"] = $code == $current ? "active" : "";
            $sm["[[CC]]"] = $code;
            $sm["[[LANG_NAME]]"] = $name;
            $s["{{SUPPORTED_LANGUAGES}}"] .= $slice->parse($sm);
        }

        return $this->getTemplate()->parse($m, $s);
    }


    public function change(): string
    {
        $params = Module::requestData();
        Cookie::set("lang_code", $params["cc"]);
        Session::clear("translation"); // String resources must be renewed in the session
        return json_encode(['result' => 1]);
    }

}