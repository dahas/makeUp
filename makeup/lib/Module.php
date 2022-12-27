<?php declare(strict_types=1);

namespace makeUp\lib;

use makeUp\lib\AccessDenied;
use makeUp\lib\RouteNotFound;
use ReflectionClass;


abstract class Module {

	protected static array $arguments = [];
	protected $config = array();
	private $className = "";
	protected $modName = "";
	protected $render = "";
	protected $dataMod = "App";
	protected $protected = 0;
	protected $history_caching = true;
	protected static $isLoggedIn = false;


	public function __construct()
	{
		$modNsArr = explode("\\", $this::class);
		$this->modName = array_pop($modNsArr);

		// Order matters!
		Config::init(self::getRoute());
		Lang::init();
		if (Config::get("cookie", "name"))
			Cookie::read(Config::get("cookie", "name"));

		// Debugging:
		if (isset($_SERVER['argc']) && $_SERVER['argc'] > 1) {
			self::$isLoggedIn = isset($_SERVER['argv'][8]) && $_SERVER['argv'][8] > 0;
		} else {
			self::$isLoggedIn = Session::get("user") > "" && Session::get("logged_in");
		}
	}

	abstract protected function build(): string;


	/**
	 * Compile and output the app as HTML.
	 */
	public function compile(): void
	{
		$this->procArguments(func_get_args());

		$params = self::getParameters();
		$route = self::getRoute();

		$render = isset($params['json']) ? "json" : "html";

		if (!isset($params['task'])) {
			$task = "build";
			Session::set("route", $route);
		} else {
			$task = $params['task'];
		}

		if ($render == "json" || $task != "build") { // Create only the Module
			$appHtml = self::create($route, $render)->$task();
		} else { // Create the whole App
			$appHtml = $this->build();
		}

		die($appHtml);
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
		$params = self::getParameters();
		$modFile = dirname(__DIR__, 1) . "/app/modules/$modName/$modName.php";

		if (is_file($modFile)) {
			$modConfig = Utils::loadIniFile($modName);
			$protected = isset($modConfig["mod_settings"]["protected"]) ? intval($modConfig["mod_settings"]["protected"]) : 0;
			if ($protected && !self::checkLogin()) {
				$module = new AccessDenied();
				$module->setRender($render);
			} else {
				require_once $modFile;
				$module = new $modName();
				$module->injectServices();
				$module->setRender($render);
				$module->setProtected($protected);
				if ($useDataMod)
					$module->setDataMod($modName);
				if ($protected)
					$module->setHistCaching(false);
			}
		} else {
			$module = new RouteNotFound();
		}

		return $module;
	}


	protected function render(string $html = ""): string
	{
		$params = self::getParameters();
		if (!isset($params['json']) || $this->getRender() == "html")
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


	protected function setHistCaching(bool $caching): void
	{
		$this->history_caching = $caching;
	}

	protected function getHistoryCaching(): bool
	{
		return $this->history_caching;
	}


	protected function getTemplate($fileName = ""): Template
	{
		$fname = $fileName ? $fileName : $this->modName . ".html";
		return Template::load($this->modName, $fname);
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


	/**
	 * Access Name of a Module.
	 * @return string
	 */
	public static function getRoute(): string
	{
		if (!empty(self::$arguments) && isset(self::$arguments['route']) &&
			isset(self::$arguments['route'][0]) && self::$arguments['route'][0]) {
			return self::$arguments['route'][0];
		} else {
			return Config::get("app_settings", "default_module");
		}
	}


	/**
	 * Access GET and POST vars in Modules.
	 * @return array
	 */
	public static function getParameters(): array
	{
		if (!empty(self::$arguments) && isset(self::$arguments['parameters'])) {
			return self::$arguments['parameters'];
		} else {
			return [];
		}
	}


	protected function setLogin(string $un): void
	{
		Session::set("logged_in", true);
		Session::set("user", $un);
	}

	public static function checkLogin(): bool
	{
		return self::$isLoggedIn;
	}

	protected function setLogout(): void
	{
		Session::set("logged_in", false);
		Session::set(Session::get("user"), null);
	}


	public function __call(string $method, mixed $args): string
	{
		return Utils::errorMessage("Task $method() not defined!");
	}
}
