<?php

namespace makeup\lib;

use DI\ContainerBuilder;


/**
 * Abstract Class Module
 * @package makeup\lib\interfaces
 */
abstract class Module
{
	protected $config = array();
	private $className = "";
	private $moduleFileName = "";


	public function __construct()
	{
		// Parameters "mod" and "task" are mandatory!
		if (!isset($_GET["mod"]) || !$_GET["mod"])
			$_GET["mod"] = Config::get("app_settings", "default_module");

		if (!isset($_GET["task"])|| !$_GET["task"])
			$_GET["task"] = "build";
		
		$modNsArr = explode("\\", get_class($this));
		$this->className = array_pop($modNsArr);
		$this->moduleFileName = Tools::camelCaseToUnderscore($this->className);

		RQ::init();
		Session::start();
		Cookie::read();
		Config::init($this->moduleFileName); // Loads config.ini
	}
	
	
	/**
	 * Run and output the app.
	 * 
	 * @return mixed|string
	 */
	public function execute()
	{
		// Debugging:
		$debugMod = "";
		$debugTask = "";
		if (isset($_SERVER['argc']) && $_SERVER['argc'] > 1) {
			$idxMod = array_search('--mod', $_SERVER['argv']);
			if ($idxMod > 0)
				$debugMod = $_SERVER['argv'][$idxMod+1];

			$idxTask = array_search('--task', $_SERVER['argv']);
			if ($idxTask > 0)
				$debugTask = $_SERVER['argv'][$idxTask+1];
		}

		// Parameter "mod" is the mandatory module name.
		$modName = $debugMod ? $debugMod : RQ::GET('mod');
		$modName = $modName ? $modName : Config::get("app_settings", "default_module");

		// Parameter "task" is mandatory, so the module knows which task to execute.
		$task = $debugTask ? $debugTask : RQ::GET('task');
		$task = $task ? $task : "build";

		// With parameter "nowrap" a module is rendered with its own template only.
		// No other HTML (neither app nor layout) is wrapped around it.
		if (!RQ::GET('wrap') && (RQ::GET('nowrap') || $task != "build")) {
			$appHtml = Module::create($modName)->$task();
		} else {
			// The app will be renderd, if it is NOT protected.
			// Or if it is protected and the user is signed in.
			$html = $this->render($modName);
			$debugPanel = Tools::renderDebugPanel();
			$appHtml = Template::html($html)->parse(["</body>" => "$debugPanel\n</body>"]);
		}

		die($appHtml);
	}


	/**
	 *
	 * @return ErrorMod|mixed
	 * @throws \Exception
	 */
	public static function create()
	{
		$args = func_get_args();
		$types = array();
		foreach ($args as $arg) {
			$types[] = gettype($arg);
		}

		// First argument must be the module name:
		if (!isset($args[0]) || $types[0] != "string" || !$args[0]) {
			throw new \Exception('Not a valid classname!');
		} else {
			$name = $args[0];
			$className = Tools::upperCamelCase($name);
		}

		$realPath = realpath(null);

		$modFile = str_replace("/public", "", str_replace("\\", "/", realpath(null))) . "/makeup/app/modules/$name/controller/$name.php";

		if (is_file($modFile)) {
			$builder = new ContainerBuilder();
			$builder->useAutowiring(false);
			$builder->useAnnotations(true);
			$container = $builder->build();
			require_once $modFile;
			return $container->get($className);
		} else {
			return new ErrorMod($className);
		}
	}


	/**
	 * Build the HTML content.
	 *
	 * @return mixed
	 */
	abstract public function build();
	
	
	/**
	 * Takes care of the setting "mod_settings|protected".
	 * If protected is set to 1 and the user isn´t logged in, 
	 * the module won´t be rendered.
	 *
	 * @return mixed|void
	 */
	public function render($modName = "")
	{
		// Deny access to a protected page as long as the user isn´t signed in.
		if (Config::get("page_settings", "protected") == "1" && (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] == false))
			die("Access denied!");
		
		if (Config::get('mod_settings', 'protected') == "1" && (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] == false))
			return null;
		
		return $this->build($modName);
	}


	/**
	 * Returns the template object
	 *
	 * @return Template|null
	 */
	public function getTemplate($fileName = "")
	{
		$fname = $fileName ? $fileName : $this->moduleFileName . ".html";
		return Template::load($this->className, $fname);
	}


	/**
	 *
	 * @param
	 *            $method
	 * @param
	 *            $args
	 * @return string
	 */
	public function __call($method, $args)
	{
		return Tools::errorMessage("Task $method() not defined!");
	}


}


/**
 * Class ErrorMod
 * 
 * @package makeup\lib
 */
class ErrorMod
{
	private $modName = "";


	public function __construct($modName)
	{
		$this->modName = strtolower("$modName");
	}


	public function render()
	{
		return Tools::errorMessage("Module '$this->modName' not found!");
	}


}

