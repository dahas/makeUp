<?php

namespace makeup\lib;

use makeup\lib\Session;

/**
 * Class Routing
 * @package makeup\lib
 */
class Routing
{
    public static function getConfig()
    {
        $mainConfig = [];

        $defaultMod = Config::get("app_settings", "default_module");
        $devMode = Config::get("app_settings", "dev_mode");

        // Create from session:
        if (!$devMode && Session::get("routing")) {
            $mainConfig = Session::get("routing");
        } 
        // Create from modules folder:
        else {
            $subConfig = [];
            
            $handle = opendir(str_replace("/public", "", str_replace("\\", "/", realpath(null))) . "/makeup/app/modules");
            while (false !== ($module = readdir($handle))) {
                if ($module != "." && $module != "..") {
                    $modIniData = Tools::loadIniFile($module);
                    if (isset($modIniData["menu"]) && isset($modIniData["menu"]["position"])) {
                        $pos = $modIniData["menu"]["position"];

                        // Sub menu
                        if (isset($modIniData["menu"]["submenu_of"]) && $modIniData["menu"]["submenu_of"]) {
                            $of = Tools::camelCaseToUnderscore($modIniData["menu"]["submenu_of"]);
                            $subConfig[$of][$pos] = self::transformIniConfig($module, $modIniData, $defaultMod);
                        }
                        // Main menu
                        else 
                        {
                            $mainConfig[$pos] = self::transformIniConfig($module, $modIniData, $defaultMod);

                            if (isset($modIniData["menu"]["submenu_of"]) && $modIniData["menu"]["submenu_of"]) {
                                $of = Tools::camelCaseToUnderscore($modIniData["menu"]["submenu_of"]);
                                $subConfig[$of][$pos] = self::transformIniConfig($module, $modIniData, $defaultMod);
                            }
                        }
                    }
                }
            }
            ksort($mainConfig);

            self::extendMainConfig($mainConfig, $subConfig);

            Session::set("routing", $mainConfig);
        }

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
            if (isset($modIniData["menu"]["route"]) && $modIniData["menu"]["route"]) {
                $route = "";
                if (substr($modIniData["menu"]["route"], 0, 1) != "/")
                    $route .= "/";
                $route .= $modIniData["menu"]["route"];
                if (substr($modIniData["menu"]["route"], -1) != "/")
                    $route .= "/";
                $ini["route"] = $route;
            } else if ($module == $defaultMod) {
                $ini["route"] = "/";
            } else {
                $ini["route"] = "/$module.html";
            }
        } 
        // No rewriting:
        else {
            if (isset($modIniData["menu"]["route"]) && $modIniData["menu"]["route"]) {
                $ini["route"] = htmlentities($modIniData["menu"]["route"]);
            } else if ($module == $defaultMod) {
                $ini["route"] = "/";
            } else {
                $ini["route"] = "?mod=$module";
            }
        }

        // Show the separator?
        $ini["separate"] = $modIniData["menu"]["separate"] ?? 0;

        // Show a header?
        $ini["header"] = $modIniData["menu"]["header"] ?? "";

        // Show a header?
        $ini["icon"] = $modIniData["menu"]["icon"] ?? "";

        // What type is the module of?
        if (isset($modIniData["mod_settings"]["type"]) && $modIniData["mod_settings"]["type"] == "MENU") {
            $ini["show_open"] = false;
        } else {
            $ini["show_open"] = true;
        }

        return $ini;
    }
}
