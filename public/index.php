<?php

/**
 * IMPORTANT: Set error reporting to 0 before deploying to production!
 */
error_reporting(E_ALL); // error_reporting(0);

require_once(str_replace("public", "", __DIR__) . 'makeup/app/controller/app.php');
$App = new makeup\app\controller\App();
$App->execute();