<?php

use makeUp\lib\Module;
use makeUp\lib\RQ;
// use makeUp\lib\Config;
use makeUp\lib\Tools;
// use makeUp\lib\Session;
// use makeUp\lib\Cookie;
// use makeUp\lib\Lang;


class Registration extends Module
{
    public function __construct()
    {
        parent::__construct();
    }


    protected function build() : string
    {
        $token = Tools::createFormToken();
        
        $m = [];
        $s = [];

        $m['##FORM_ACTION##'] = Tools::linkBuilder($this->modName, "register");
        $m['##TOKEN##'] = $token;
        $m['##REDIRECT##'] = $this->modName;

        return $this->render($m, $s);
    }


	public function register()
	{
        // ToDo: Registration process
        header("Location: " . Tools::linkBuilder(RQ::post("redirect")));
	}

}
