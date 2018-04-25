<?php

namespace makeup\lib;

/**
 * Class Lang
 * @package makeup\lang
 */
class Lang
{
    private static $strings = array();

    public static function init($moduleFileName = "app")
    {
        $strings = Tools::loadJsonFile($moduleFileName);
        if (empty(self::$strings)) {
            $appLang = Tools::loadJsonFile();
        } else {
            $appLang = self::$strings;
        }

        if ($moduleFileName != "app") {
            if ($modLang = Tools::loadJsonFile($moduleFileName)) {
                $appLang = Tools::arrayMerge($appLang, $modLang);
            }
        }

        self::$strings = $appLang;
    }

    /**
     * Returns a translated string resource
     * @return string|null
     */
    public static function get()
    {
        $args = func_get_args();

        if (count($args) == 2) {
            $mod = $args[0];
            $string = $args[1];
        } else {
            $backtrace = debug_backtrace();
            $mod = str_replace(".php", "", basename($backtrace[0]["file"]));
            $string = $args[0];
        }

        $lang = RQ::get("lang") && isset(self::$strings[$mod][RQ::get("lang")]) ? RQ::get("lang") : Config::get("app_settings", "default_lang");
        return self::$strings[$mod][$lang][$string] ?? null;
    }

}
