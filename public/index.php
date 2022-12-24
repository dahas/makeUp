<?php

use makeUp\App;
use makeUp\lib\Router;

require dirname(__DIR__, 1) . "/makeup/vendor/autoload.php";

if (isset($_SERVER['HTTP_HOST']) && ($_SERVER['HTTP_HOST'] === "localhost:2400")) {
    error_reporting(E_ALL);
} else {
    error_reporting(0);
}

$Router = new Router();
$Router->get("/", [App::class, "compile"])->run();
