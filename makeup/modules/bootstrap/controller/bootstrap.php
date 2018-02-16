<?php

use makeup\lib\Module;
use makeup\lib\Tools;
use makeup\lib\Routing;


/**
 * The name of a modules class must always be UpperCamelCase!
 * But when you create a module, you must use the name of the
 * class file (without the extension ".php").
 *
 * Class Home
 */
class Bootstrap extends Module
{
	/**
	 * Calling the parent constructor is mandatory!
	 */
	public function __construct()
	{
		parent::__construct();
	}


	/**
	 * The mandatory build function.
	 * 
	 * @return mixed|string
	 */
	public function build()
	{
		return $this->getTemplate()->parse();
	}

}

