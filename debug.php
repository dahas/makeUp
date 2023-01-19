<?php

use makeUp\App;
use makeUp\lib\FrontController;

require __DIR__ . "/makeup/vendor/autoload.php";

error_reporting(E_ALL);

$_SERVER['REQUEST_METHOD'] = "GET";
$_SERVER['REQUEST_URI'] = "/Home";
$_SERVER['HTTP_X_MAKEUP_AJAX'] = 0;

// $_REQUEST['param1'] = "Post data 1";

$_SESSION["logged_in"] = false;

$fc = new FrontController();
$fc->handle();
