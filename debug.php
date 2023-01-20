<?php

use makeUp\App;
use makeUp\src\Request;
use makeUp\src\Response;

require __DIR__ . "/makeup/vendor/autoload.php";

error_reporting(E_ALL);

$_SERVER['REQUEST_METHOD'] = "GET";
// $_SERVER['REQUEST_URI'] = "/SampleData/getItem?uid=6";
$_SERVER['REQUEST_URI'] = "/Home";
$_SERVER['HTTP_X_MAKEUP_AJAX'] = 0;

// $_POST['param1'] = "Post data 1";

$_SESSION["logged_in"] = false;

$App = new App();
$App->handle(new Request(), new Response());