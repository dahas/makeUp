<?php

namespace makeUp\lib;

use ReflectionClass;


abstract class Module {
	protected $config = array();
	private $className = "";
	protected $modName = "";

	public function __construct()
	{
		$modNsArr = explode("\\", get_class($this));
		$this->className = array_pop($modNsArr);
		$this->modName = Tools::camelCaseToUnderscore($this->className);

		// Order matters!
		Session::start(); // 1st
		Config::init($this->modName); // 2nd
		RQ::init(); // 3rd
		Lang::init(); // 4th

		if (Config::get("cookie", "name"))
			Cookie::read(Config::get("cookie", "name")); // 5th
	}

	/**
	 * Run and output the app.
	 */
	public function execute(): void
	{
		// Debugging:
		if (isset($_SERVER['argc']) && $_SERVER['argc'] > 1) {
			$idxMod = array_search('--mod', $_SERVER['argv']);
			if ($idxMod > 0)
				$_GET['mod'] = $_SERVER['argv'][$idxMod + 1];

			$idxTask = array_search('--task', $_SERVER['argv']);
			if ($idxTask > 0)
				$_GET['task'] = $_SERVER['argv'][$idxTask + 1];

			$idxRender = array_search('--render', $_SERVER['argv']);
			if ($idxRender > 0)
				$_GET['render'] = $_SERVER['argv'][$idxRender + 1];

			RQ::init();
		}

		// Parameter "mod" is the mandatory module name
		$modName = RQ::GET('mod') ?: Config::get("app_settings", "default_module");

		// Parameter "task" is mandatory, so the module knows which task to execute
		$task = RQ::GET('task') ?: "build";

		// Parameter "render" is optional
		$render = RQ::GET('render') ?: "html";

		// With parameter render="json" a module is rendered as an object with metadata and its own slice template only.
		if ($render != "html" && ($render == "json" || $task != "build")) {
			Config::init($modName);
			if (Config::get("mod_settings", "protected") == "1" && (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] == false))
				die("Access denied!");
			$appHtml = Module::create($modName)->$task();
		} else {
			$html = $this->build();
			$debugPanel = Tools::renderDebugPanel();
			$appHtml = Template::html($html)->parse(["</body>" => "$debugPanel\n</body>"]);
		}

		die($appHtml);
	}

	/**
	 * Creates an object as long the user has permission to access the module.
	 */
	public static function create(): mixed
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

		$realPath = realpath('');

		$modFile = dirname(__DIR__, 1) . "/modules/$name/controller/$name.php";

		if (is_file($modFile)) {
			$modConfig = Tools::loadIniFile($name);
			$protected = isset($modConfig["mod_settings"]["protected"]) ? intval($modConfig["mod_settings"]["protected"]) : 0;
			if ($protected && (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] == false))
				return new AccessDeniedMod($className);

			require_once $modFile;
			$module = new $className();
			$module->injectServices();
			return $module;
		} else {
			return new ErrorMod($className);
		}
	}

	protected function injectServices()
	{
		$rc = new ReflectionClass(get_class($this));
		$properties = $rc->getProperties();
		foreach ($properties as $property) {
			$pName = $property->name;
			foreach ($property->getAttributes() as $attribute) {
				$service = $attribute->newInstance()->service;
				$sName = 'makeUp\\services\\' . $service;
				$this->$pName = new $sName();
			}
		}
	}

	abstract protected function build(): string;

	protected function getTemplate($fileName = ""): Template
	{
		$fname = $fileName ? $fileName : $this->modName . ".html";
		return Template::load($this->className, $fname);
	}

	protected function render(string $html = ""): string
	{
		if (!RQ::GET('render') || RQ::GET('render') == 'html')
			return $html;

		$json = json_encode([
			"title" => Config::get("page_settings", "title"),
			"module" => $this->modName,
			"segments" => [
				[
					"html" => $html,
					"target" => 'content'
				]
			]
		]);

		return $json;
	}

	public function __call(string $method, mixed $args): string
	{
		return Tools::errorMessage("Task $method() not defined!");
	}
}


class ErrorMod {
	private $modName = "";

	public function __construct($modName)
	{
		$this->modName = strtolower("$modName");
	}

	public function build(): string
	{
		return Tools::errorMessage("Module '$this->modName' not found!");
	}
}


class AccessDeniedMod {
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