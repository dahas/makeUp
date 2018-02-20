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
        $mainConfig = [];
        $subConfig = [];

        $defaultMod = Config::get("app_settings", "default_module");

        $handle = opendir(str_replace("/public", "", str_replace("\\", "/", realpath(null))) . "/makeup/modules");
        while (false !== ($module = readdir($handle))) {
            if ($module != "." && $module != "..") {
                $modIniData = Tools::loadIniFile($module);
                if (isset($modIniData["menu"]) && isset($modIniData["menu"]["position"])) {
                    $pos = $modIniData["menu"]["position"];

                    // Sub menu
                    if (isset($modIniData["menu"]["submenu_of"]) && $modIniData["menu"]["submenu_of"]) {
                        $of = $modIniData["menu"]["submenu_of"];
                        $subConfig[$of][$pos] = self::transformIniConfig($module, $modIniData, $defaultMod);
                    }
                    // Main menu
                    else 
                    {
                        $mainConfig[$pos] = self::transformIniConfig($module, $modIniData, $defaultMod);

                        if (isset($modIniData["menu"]["submenu_of"]) && $modIniData["menu"]["submenu_of"]) {
                            $of = $modIniData["menu"]["submenu_of"];
                            $subConfig[$of][$pos] = self::transformIniConfig($module, $modIniData, $defaultMod);
                        }
                    }
                }
            }
        }

        ksort($mainConfig);

        self::extendMainConfig($mainConfig, $subConfig);

        return $mainConfig;
    }

    /**
     * Extends main items with a submenu.
     */
    private static function extendMainConfig(&$mainConfig, $subConfig) {
        foreach ($subConfig as $module => $subIni) {
            if (self::extendSubmenuConfig($mainConfig, $subConfig, $module, $subIni)) {
                self::extendMainConfig($mainConfig, $subConfig);
            }
        }
    }

    /**
     * Extends submenu items with a submenu.
     */
    private static function extendSubmenuConfig(&$mainConfig, $subConfig, $module, $subIni)
    {
        $extended = false;
        foreach ($mainConfig as $pos => $mainIni) {
            if (!isset($mainConfig[$pos]["submenu"])) {
                if ($mainConfig[$pos]["module"] == $module) {
                    ksort($subIni);
                    $mainConfig[$pos]["submenu"] = $subIni;
                    $extended = true;
                }
            } else {
                if (self::extendSubmenuConfig($mainConfig[$pos]["submenu"], $subConfig, $module, $subIni)) {
                    self::extendMainConfig($mainConfig[$pos]["submenu"], $subConfig);
                }
            }
        }

        return $extended;
    }

    /**
     * Read ini file
     */
    private static function transformIniConfig($module, $modIniData, $defaultMod)
    {
        $ini = [];

        $ini["module"] = $module;

        // Menu item text
        if (isset($modIniData["menu"]["text"]) && $modIniData["menu"]["text"]) {
            $ini["text"] = $modIniData["menu"]["text"];
        } else if (isset($modIniData["page_settings"]["title"]) && $modIniData["page_settings"]["title"]) {
            $ini["text"] = $modIniData["page_settings"]["title"];
        } else {
            $ini["text"] = $module;
        }

        // Rewriting enabled:
        if (Config::get("app_settings", "url_rewriting")) {
            if (isset($modIniData["menu"]["params"]) && $modIniData["menu"]["params"]) {
                $ini["route"] = htmlentities("$module.html" . "&" . $modIniData["menu"]["params"]);
            } else if ($module == $defaultMod) {
                $ini["route"] = "/";
            } else {
                $ini["route"] = "$module.html";
            }
        } 
        // No rewriting:
        else {
            if (isset($modIniData["menu"]["params"]) && $modIniData["menu"]["params"]) {
                $ini["route"] = htmlentities("?mod=$module" . "&" . $modIniData["menu"]["params"]);
            } else if ($module == $defaultMod) {
                $ini["route"] = "/";
            } else {
                $ini["route"] = "?mod=$module";
            }
        }

        if (isset($modIniData["menu"]["separate"]) && $modIniData["menu"]["separate"]) {
            $ini["separate"] = 1;
        } else {
            $ini["separate"] = 0;
        }

        if (isset($modIniData["menu"]["header"]) && $modIniData["menu"]["header"]) {
            $ini["header"] = $modIniData["menu"]["header"];
        } else {
            $ini["header"] = "";
        }

        if (RQ::GET("mod") == $module) {
            $ini["active"] = 1;
        } else {
            $ini["active"] = 0;
        }

        return $ini;
    }
}
