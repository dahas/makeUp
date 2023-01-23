<?php

use makeUp\App;
use makeUp\src\Request;

require __DIR__ . "/makeup/vendor/autoload.php";

error_reporting(E_ALL);

$_SERVER['REQUEST_METHOD'] = "GET";
// $_SERVER['REQUEST_URI'] = "/SampleData/getItem?uid=6";
// $_SERVER['REQUEST_URI'] = "/Home";
$_SERVER['REQUEST_URI'] = "/Authentication/buildLogoutForm";
$_SERVER['HTTP_X_MAKEUP_AJAX'] = 1;

// $_POST['param1'] = "Post data 1";

$_SESSION["logged_in"] = true;

$App = new App();
$App->handle(new Request());