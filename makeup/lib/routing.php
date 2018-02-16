<?php

namespace makeup\lib;

/**
 * Class Routing
 *
 * @package makeup\lib
 */
class Routing
{
    public static function getConfig()
    {
        $ini = [];
        $defaultMod = Config::get("app_settings", "default_module");

        $handle = opendir(str_replace("/public", "", str_replace("\\", "/", realpath(null))) . "/makeup/modules");
        while (false !== ($module = readdir($handle))) {
            if ($module != "." && $module != "..") {
                $modIni = Tools::loadIniFile($module);
                if (isset($modIni["menu"]) && isset($modIni["menu"]["position"])) {
                    $pos = $modIni["menu"]["position"];
    
                    // Menu item text
                    if (isset($modIni["menu"]["text"]) && $modIni["menu"]["text"]) {
                        $ini[$pos]["text"] = $modIni["menu"]["text"];
                    } else if (isset($modIni["page_settings"]["title"]) && $modIni["page_settings"]["title"]) {
                        $ini[$pos]["text"] = $modIni["page_settings"]["title"];
                    } else {
                        $ini[$pos]["text"] = ucfirst($module);
                    }
    
                    if (isset($modIni["menu"]["params"]) && $modIni["menu"]["params"]) {
                        $ini[$pos]["route"] = htmlentities("?mod=$module" . "&" . $modIni["menu"]["params"]);
                    } else if ($module == $defaultMod) {
                        $ini[$pos]["route"] = "/";
                    } else {
                        $ini[$pos]["route"] = "?mod=$module";
                    }
    
                    if (RQ::GET("mod") == $module) {
                        $ini[$pos]["active"] = 1;
                    } else {
                        $ini[$pos]["active"] = 0;
                    }
                }
            }
        }

        ksort($ini);

        return $ini;
    }
}
