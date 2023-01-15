<?php declare(strict_types=1);

namespace makeUp\lib;

use makeUp\lib\AccessDenied;
use makeUp\lib\RouteNotFound;
use ReflectionClass;


abstract class Module {

	protected static array $arguments = [];
	protected $config = array();
	protected $modName = "";
	protected $render = "";
	protected $isXHR = false;
	protected $dataMod = "App";
	protected $protected = 0;
	protected static $isLoggedIn = false;


	public function __construct()
	{
		$modNsArr = explode("\\", $this::class);
		$this->modName = array_pop($modNsArr);

		// Order matters!
		Config::init(self::name());
		Lang::init();
		if (Config::get("cookie", "name"))
			Cookie::read(Config::get("cookie", "name"));

		// Debugging:
		if (isset($_SERVER['argc']) && $_SERVER['argc'] > 1) {
			self::$isLoggedIn = isset($_SERVER['argv'][8]) && $_SERVER['argv'][8] > 0;
			$this->isXHR = isset($_SERVER['argv'][10]) && $_SERVER['argv'][10] > 0;
		} else {
			self::$isLoggedIn = Session::get("logged_in") && Session::get("logged_in") === true;
		}
	}

	abstract protected function build(): string;


	/**
	 * Compile and output the app as HTML.
	 */
	public function compile(): void
	{
		$this->procArguments(func_get_args());

		$modName = self::name();
		$task = self::task();

		if(!Session::get("routeMod")) {
			Session::set("routeMod", $modName);
		}

		if(function_exists("getallheaders")) {
			$this->procHttpRequest(getallheaders());
		}

		$render = $this->isXHR ? "json" : "html";

		if ($render == "json" || $task != "build") { // Create only the Module
			$appHtml = self::create($modName, $render)->$task();
		} else { // Create the whole App
			$appHtml = $this->build();
		}

		die($appHtml);
	}


	/**
	 * Make GET and POST vars available in Modules.
	 * @param array $args
	 * @return void
	 */
	protected function procArguments(array $args): void
	{
		self::$arguments = isset($args[0]) && $args[0] ? $args[0] : $args;
	}


	protected function procHttpRequest(array $headers): void
	{
		if (isset($headers['X-makeUp-Route'])) {
			$routeArr = explode("/", $headers['X-makeUp-Route']);
			array_shift($routeArr);
			if ($routeArr[0]) {
				Session::set("routeMod", $routeArr[0]);
			} else {
				Session::set("routeMod", "Home");
			}
		}
		// print_r($headers);
		$this->isXHR = isset($headers['X-makeUp-Ajax']) || isset($headers['X-Requested-With']);

	}


	/**
	 * Creates an object of a module.
	 * @param mixed $modName
	 * @param mixed $render
	 * @param mixed $dataMod
	 * @return mixed
	 */
	public static function create(string $modName, string $render = "html", bool $useDataMod = false): mixed
	{
		$params = self::requestData();
		$modFile = dirname(__DIR__, 1) . "/app/modules/$modName/$modName.php";

		if (is_file($modFile)) {
			$modConfig = Utils::loadIniFile($modName);
			$protected = isset($modConfig["mod_settings"]["protected"]) ? intval($modConfig["mod_settings"]["protected"]) : 0;
			if ($protected && !self::checkLogin()) {
				$module = new AccessDenied();
				$module->setRender($render);
				$module->setProtected($protected);
			} else {
				require_once $modFile;
				$module = new $modName();
				$module->injectServices();
				$module->setRender($render);
				$module->setProtected($protected);
				if ($useDataMod)
					$module->setDataMod($modName);
			}
		} else {
			$module = new RouteNotFound();
		}

		return $module;
	}


	protected function render(string $html): string
	{
		if ($this->getRender() == "html")
			return $html;
		else
			return $this->renderJSON($html);
	}


	/**
	 * Returns meta data of a page as a JSON Object.
	 * @param string $html HTML content if no $dataMod is set as target.
	 * @return string JSON Object
	 */
	protected function renderJSON(string $html = ""): string
	{
		return json_encode([
			"protected" => Config::get("mod_settings", "protected"),
			"title" => Config::get("page_settings", "title"),
			"metatags" => Config::get("metatags"),
			"meta_http_equiv" => Config::get("meta_http_equiv"),
			"caching" => false, // $this->getHistoryCaching(),
			"module" => $this->modName,
			"content" => $html
		]);
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


	protected function setRender(string $render = ""): void
	{
		$this->render = $render;
	}

	protected function getRender(): string
	{
		return $this->render;
	}


	public function setDataMod(string $mod): void
	{
		$this->dataMod = $mod;
	}

	public function getDataMod(): string
	{
		return $this->dataMod;
	}


	protected function setProtected(int $protected = 0): void
	{
		$this->protected = $protected;
	}

	protected function isProtected(): int
	{
		return $this->protected;
	}


	protected function getTemplate($fileName = ""): Template
	{
		$fname = $fileName ? $fileName : $this->modName . ".html";
		return Template::load($this->modName, $fname);
	}


	protected function routeMod(): string
	{
		return Session::get("routeMod");
	}


	/**
	 * Access Name of a Module.
	 * @return string
	 */
	protected static function name(): string
	{
		if (!empty(self::$arguments) && isset(self::$arguments['module']) &&
			isset(self::$arguments['module'][0]) && self::$arguments['module'][0]) {
			return self::$arguments['module'][0];
		} else {
			return Config::get("app_settings", "default_module");
		}
	}


	/**
	 * Access Name of a Task.
	 * @return string
	 */
	protected static function task(): string
	{
		if (!empty(self::$arguments) && isset(self::$arguments['module']) &&
			isset(self::$arguments['module'][1]) && self::$arguments['module'][1]) {
			return self::$arguments['module'][1];
		} else {
			return "build";
		}
	}


	/**
	 * Access GET and POST vars in Modules.
	 * @return array
	 */
	public static function requestData(): array
	{
		if (!empty(self::$arguments) && isset(self::$arguments['parameters'])) {
			return self::$arguments['parameters'];
		} else {
			return [];
		}
	}


	/**
	 * Use this function to grant or deny a user access to protected features and content.
	 * @param bool $verified
	 * @return void
	 */
	protected function auth(bool $verified): void
	{
		session_regenerate_id(true);
		Session::set("logged_in", $verified);
	}
	

	public static function checkLogin(): bool
	{
		return Session::get("logged_in") ?? false;
	}


	public function __call(string $method, mixed $args): string
	{
		return Utils::errorMessage("Task $method() not defined!");
	}
}
