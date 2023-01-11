<?php declare(strict_types = 1);

namespace makeUp\lib;


final class Lang {
    private static $strings = array();


    public static function init()
    {
        if (empty(self::$strings)) {
            $appLang = Utils::getTranslation();
        } else {
            $appLang = self::$strings;
        }

        self::$strings = $appLang;
    }


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

        // Is the translated string for the module available?
        if (isset(self::$strings["translation"][$mod][$string]))
            return self::$strings["translation"][$mod][$string];

        // Is the translated string available on a global level?
        if (isset(self::$strings["translation"]["App"][$string]))
            return self::$strings["translation"]["App"][$string];

        // Is a default string for the module available?
        if (isset(self::$strings["default"][$mod][$string]))
            return self::$strings["default"][$mod][$string];

        // Is a default string available on a global level?
        if (isset(self::$strings["default"]["App"][$string]))
            return self::$strings["default"]["App"][$string];

        return '';
    }

}