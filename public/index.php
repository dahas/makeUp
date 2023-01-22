<?php

/*******************************************************************************
 *                         _        _   _                                      *
 *         _ __ ___   __ _| | _____| | | |_ __                                 *
 *        | '_ ` _ \ / _` | |/ / _ \ | | | '_ \                                *
 *        | | | | | | (_| |   <  __/ |_| | |_) |                               *
 *        |_| |_| |_|\__,_|_|\_\___|\___/| .__/                                *
 *                                       |_|                                   *
 *                                                                             *
 *   makeUp is a PHP framework to build a Bootstrap single page application.   *
 *                                                                             * 
 *******************************************************************************/

use makeUp\App;
use makeUp\src\Request;

require dirname(__DIR__, 1) . "/makeup/vendor/autoload.php";

if (isset($_SERVER['HTTP_HOST']) && ($_SERVER['HTTP_HOST'] === "localhost:2400")) {
    error_reporting(E_ALL);
} else {
    error_reporting(0);
}

$App = new App();
$App->handle(new Request());