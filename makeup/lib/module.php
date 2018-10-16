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

		// Renew translated strings in session when language has changed:
		if (RQ::get("change_lang"))
			Tools::changeTranslation();
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

		// With parameter app="nowrap" a module is rendered with its own slice template only.
		if (RQ::GET('app') != "wrap" && (RQ::GET('app') == "nowrap" || $task != "build")) {
			Config::init($modName);
			if (Config::get("mod_settings", "protected") == "1" && (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] == false))
				die("Access denied!");
			$appHtml = Module::create($modName)->$task();
		} else {
			if (RQ::GET('app') == "wrap" && $task != "build") {
				$html = $this->build($modName, $task);
			} 
			else {
				$html = $this->build($modName);
			}
			$debugPanel = Tools::renderDebugPanel();
			$appHtml = Template::html($html)->parse(["</body>" => "$debugPanel\n</body>"]);
		}

		die($appHtml);
	}


	/**
	 * Creates an object as long the user has permission to access the module.
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

		$modFile = str_replace("/public", "", str_replace("\\", "/", realpath(null))) . "/makeup/modules/$name/controller/$name.php";

		if (is_file($modFile)) {
			$modConfig = Tools::loadIniFile($name);
			$protected = isset($modConfig["mod_settings"]["protected"]) ? intval($modConfig["mod_settings"]["protected"]) : 0;
			if ($protected && (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] == false))
				return new AccessDeniedMod($className);
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


	public function build()
	{
		return Tools::errorMessage("Module '$this->modName' not found!");
	}


}


/**
 * Class AccessDeniedMod
 * 
 * @package makeup\lib
 */
class AccessDeniedMod
{
	private $modName = "";


	public function __construct($modName)
	{
		$this->modName = strtolower("$modName");
	}


	public function build()
	{
		return null;
	}


}

