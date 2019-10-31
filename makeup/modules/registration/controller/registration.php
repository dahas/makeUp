<?php

use makeup\lib\Module;
use makeup\lib\RQ;
// use makeup\lib\Config;
use makeup\lib\Tools;
// use makeup\lib\Session;
// use makeup\lib\Cookie;
// use makeup\lib\Lang;


class Registration extends Module
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * This function prepares the module for rendering.
     *
     * @param string $modName
     * @return string
     */
    protected function build() : string
    {
        $token = Tools::createFormToken();
        
        $m = [];
        $s = [];

        $m['##FORM_ACTION##'] = Tools::linkBuilder($this->modName, "register");
        $m['##TOKEN##'] = $token;
        $m['##REDIRECT##'] = $this->modName;

        return $this->getTemplate()->parse($m, $s);
    }


    /**
     * Registration process
	 */
	public function register()
	{
        // ToDo: Registration process
        header("Location: " . Tools::linkBuilder(RQ::post("redirect")));
	}

}
