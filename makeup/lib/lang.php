<?php

namespace makeUp\lib;

/**
 * Class Lang
 * @package makeUp\lang
 */
class Lang
{
    private static $strings = array();

    public static function init()
    {
        if (empty(self::$strings)) {
            $appLang = Tools::getTranslation();
        } else {
            $appLang = self::$strings;
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

        return self::$strings[$mod][$string] ?? null;
    }

}
