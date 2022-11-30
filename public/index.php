<?php

if ($_SERVER['HTTP_HOST'] === "localhost:2400") {
    error_reporting(E_ALL);
} else {
    error_reporting(0);
}

require_once(str_replace("public", "", __DIR__) . 'makeup/app/controller/app.php');

$App = new makeup\app\controller\App();

$App->execute();
