<?php

use makeUp\App;
use makeUp\lib\Router;

require __DIR__ . "/makeup/vendor/autoload.php";

error_reporting(E_ALL);

$_SERVER['REQUEST_METHOD'] = "GET";
$_SERVER['REQUEST_URI'] = "/Skincare";
$_SERVER['HTTP_X_MAKEUP_AJAX'] = 0;

$_POST['param1'] = "Post data 1";
$_POST['param2'] = "Post data 2";

$_SESSION["logged_in"] = true;

$Router = new Router();
$Router->get("/", [App::class, "compile"])->run();
