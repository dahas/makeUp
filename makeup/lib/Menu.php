<?php declare(strict_types = 1);

namespace makeUp\lib;

use makeUp\lib\Session;


class Menu 
{
    public static function getConfig() : array
    {
        $json = file_get_contents(dirname(__DIR__, 1) . '/menu.json');
        $mainConfig = json_decode($json);

        Session::set("routing", $mainConfig);

        return $mainConfig;
    }
}