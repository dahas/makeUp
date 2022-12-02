<?php

namespace makeUp\lib;

use makeUp\lib\Session;


class Routing 
{
    public static function getConfig() : array
    {
        $json = file_get_contents(dirname(__DIR__, 1) . '/menu.json');
        $mainConfig = json_decode($json);

        Session::set("routing", $mainConfig);

        return $mainConfig;
    }
}