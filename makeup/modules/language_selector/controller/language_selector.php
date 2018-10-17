<?php

use makeup\lib\Module;
use makeup\lib\RQ;
use makeup\lib\Config;
use makeup\lib\Tools;
use makeup\lib\Template;
use makeup\lib\Cookie;
use makeup\lib\Session;


/**
 * This is a system module
 */
class LanguageSelector extends Module
{
    public function __construct()
    {
        parent::__construct();
    }

    
    protected function build() : string
    {
        $m = [];
        $s = [];

        $suppLangs = Tools::getSupportedLanguages();
        $current = Cookie::get("lang_code") ?? Tools::getUserLanguageCode();

        $m["##CURRENT_LANGUAGE##"] = $suppLangs[$current];

        $slice = $this->getTemplate()->getSlice("{{SUPPORTED_LANGUAGES}}");

        $s["{{SUPPORTED_LANGUAGES}}"] = "";

        foreach ($suppLangs as $code => $name) {
            $sm = [];
            $sm["##ACTIVE##"] = $code == $current ? "active" : "";
            $sm["##LINK##"] = Tools::linkBuilder($this->modName, "change_language", ["referer" => RQ::get("mod"), "lang_code" => $code]);
            $sm["##LANG_NAME##"] = $name;
            $s["{{SUPPORTED_LANGUAGES}}"] .= $slice->parse($sm);
        }

        return $this->getTemplate()->parse($m, $s);
    }


    public function change_language()
    {
        Cookie::set("lang_code", RQ::get("lang_code"));
        Session::clear("translation"); // String resources must be renewed in the session
        header("Location: " . Tools::linkBuilder(RQ::get("referer")));
    }

}
