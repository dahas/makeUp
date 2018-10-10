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
	private $modName = "";


	public function __construct()
	{
		$modNsArr = explode("\\", get_class($this));
		$this->className = array_pop($modNsArr);
		$this->modName = Tools::camelCaseToUnderscore($this->className);

		// Order matters!
		Session::start(); // 1st
		Config::init($this->modName); // 2nd
		RQ::init(); // 3rd
		Lang::init($this->modName); // 4th
		
		if (Config::get("cookie", "name"))
			Cookie::read(Config::get("cookie", "name")); // 5th
	}
	
	
	/**
	 * Run and output the app.
	 * 
	 * @return string
	 */
	public function execute() : string
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
		$modName = $debugMod ?: RQ::GET('mod');
		$modName = $modName ?: Config::get("app_settings", "default_module");

		// Parameter "task" is mandatory, so the module knows which task to execute.
		$task = $debugTask ?: RQ::GET('task');
		$task = $task ?: "build";

		// With parameter app="nowrap" a module is rendered with its own template only.
		// No other HTML (neither app nor layout) is wrapped around it.
		if (RQ::GET('app') != "wrap" && (RQ::GET('app') == "nowrap" || $task != "build")) {
			$appHtml = Module::create($modName)->$task();
		} 
		// The app will be renderd, if it is NOT protected.
		// Or if it is protected and the user is signed in.
		else {
			if (RQ::GET('app') == "wrap" && $task != "build") {
				$html = $this->render($modName, $task);
			} 
			else {
				$html = $this->render($modName);
			}
			$debugPanel = Tools::renderDebugPanel();
			$appHtml = Template::html($html)->parse(["</body>" => "$debugPanel\n</body>"]);
		}

		die($appHtml);
	}


	/**
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

		$modFile = str_replace("/public", "", str_replace("\\", "/", realpath(null))) . "/makeup/modules/$name/controller/$name.php";

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
	abstract protected function build() : string;
	
	
	/**
	 * Takes care of the setting "mod_settings|protected".
	 * If protected is set to 1 and the user isn´t logged in, 
	 * the module won´t be rendered.
	 *
	 * @return mixed|void
	 */
	protected function render($modName = "", $task = "") : string
	{
		// Deny access to a protected page as long as the user isn´t signed in.
		if (Config::get("page_settings", "protected") == "1" && (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] == false))
			die("Access denied!");
		
		if (Config::get('mod_settings', 'protected') == "1" && (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] == false))
			return null;
		
		return $this->build($modName, $task);
	}


	/**
	 * Returns the template object
	 *
	 * @return Template
	 */
	protected function getTemplate($fileName = "") : Template
	{
		$fname = $fileName ? $fileName : $this->modName . ".html";
		return Template::load($this->className, $fname);
	}


	/**
	 * @param $method
	 * @param $args
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

