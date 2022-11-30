<?php

namespace makeup\lib;

use makeup\lib\Session;

/**
 * Class Routing
 * @package makeup\lib
 */
class Routing {
    public static function getConfig() {
        $json = file_get_contents(dirname(__DIR__, 1) . '/menu.json');
        $mainConfig = json_decode($json);

        Session::set("routing", $mainConfig);

        return $mainConfig;
    }
}