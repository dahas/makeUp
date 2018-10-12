<?php

use makeup\lib\Module;
use makeup\lib\RQ;
use makeup\lib\Config;
use makeup\lib\Tools;
use makeup\lib\Template;
use makeup\lib\Cookie;

class LanguageSelector extends Module
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function build($modName = "") : string
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
            $sm["##LANG_CODE##"] = $code;
            $sm["##LANG_NAME##"] = $name;
            $s["{{SUPPORTED_LANGUAGES}}"] .= $slice->parse($sm);
        }

        return $this->getTemplate()->parse($m, $s);
    }

}
