<?php

use makeUp\App;
use makeUp\lib\Router;

require dirname(__DIR__, 1) . "/makeup/vendor/autoload.php";

// if (isset($_SERVER['HTTP_HOST']) && ($_SERVER['HTTP_HOST'] === "localhost:2400" || $_SERVER['HTTP_HOST'] === "makeup.loc")) {
    error_reporting(E_ALL);
// } else {
//     error_reporting(0);
// }

## SET FOR DEBUGGER. COMMENT OUT IN BROWSER! ##
## ----------------------------------------- ##
if (isset($_SERVER['argc']) && $_SERVER['argc'] > 0) {
    $_SERVER['REQUEST_URI'] = "/skincare?render=json";
    // $_SERVER['REQUEST_URI'] = "/index";
    $_SERVER['REQUEST_METHOD'] = "GET";
    $_POST['user'] = "Test data";
}
## ----------------------------------------- ##

$Router = new Router();
$Router->get("/", [App::class, "execute"])->run();
