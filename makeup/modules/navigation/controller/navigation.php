<?php

/**
 * Include libraries like below.
 * (Module is mandatory!)
 */
use makeup\lib\Module;
use makeup\lib\RQ;
use makeup\lib\Routing;
use makeup\lib\Config;
use makeup\lib\Tools;
use makeup\lib\Template;

/**
 * Class names of modules always have to be UpperCamelCase.
 * But when you create a module, all chars are lowercase
 * and parts are connected with an underscore: Module::create("lower_case")
 */
class Navigation extends Module
{
    /**
     * Calling the parent constructor is required!
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * This is the manatory default task. It is required
     * to render the template. It returns pure HTML.
     *
     * @param string $modName
     * @return string
     */
    public function build($modName = "")
    {
        $menuSlice = $this->getTemplate()->getSlice("##MENU##");
        $s["##MENU##"] = "";

        $routing = Routing::getConfig();
        
        foreach ($routing as $item => $data)
        {
            $s["##MENU##"] .= $menuSlice->parse([
                "##LINK##" => $data["route"],
                "##TEXT##" => $data["text"],
                "##ACTIVE##" => $data["active"] ? "class=\"active\"" : ""
            ]);
        }

        return $this->getTemplate()->parse([], $s);
    }

    /**
	 * This is a custom task. It is actually a simple method :-).
     * Tasks can be named what ever you want. Use them e.g. to
     * request asynchronous data.
     *
     * A task can be executed via URL:
     * Just add "?mod=navigation&task=ask" to the URL to see the result.
	 *
	 * @return mixed|string
	 */
	public function ask()
	{
        $data = ["When was", "the last time", "you looked at", "the starry sky?"];
		return json_encode($data);
	}

}
