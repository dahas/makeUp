<?php

error_reporting(E_ALL);

require_once(str_replace("public", "", __DIR__) . 'makeup/app/controller/app.php');
$App = new makeup\app\controller\App();
$App->execute();