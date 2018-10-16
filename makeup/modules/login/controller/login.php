<?php

/**
 * Include libraries like below.
 * (Module is mandatory!)
 */
use makeup\lib\Module;
use makeup\lib\RQ;
use makeup\lib\Config;
use makeup\lib\Tools;
use makeup\lib\Template;

/**
 * Although names of modules are always UpperCamelCase you instatiate them with names that are lowercase 
 * and which parts are connected with underscores. E.g. Module::create("lower_case")
 */
class Login extends Module
{
    /**
     * Calling the parent constructor is mandatory!
     */
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
    protected function build($modName = "") : string
    {
        // Replace markers like this:
        $marker["##MODULE##"] = RQ::GET("mod"); // Use of RQ::GET() or RQ::POST() instead of the superglobals for security reasons 

        return $this->getTemplate()->parse($marker);
    }

    /**
     * This is an example task.
	 *
	 * @return mixed|string
	 */
	// public function ask()
	// {
    //     $data = ["When was", "the last time", "you looked at", "the starry sky?"];
	// 	   return json_encode($data);
	// }

}
