<?php declare(strict_types = 1);

namespace makeUp\lib;

use ReflectionClass;


abstract class Module {
	protected $config = array();
	private $className = "";
	protected $modName = "";
	protected $render = "";
	protected static $isLoggedIn = false;

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

		// Debugging:
		if (isset($_SERVER['argc']) && $_SERVER['argc'] > 1) {
			for ($n = 0; $n < $_SERVER['argc']; $n++) {
				if (str_contains($_SERVER['argv'][$n], "--")) {
					$_GET[substr($_SERVER['argv'][$n], 2)] = $_SERVER['argv'][$n + 1];
				}
			}

			self::$isLoggedIn = isset($_GET['auth']) && $_GET['auth'] > 0;

			RQ::init();
		} else {
			self::$isLoggedIn = Session::get("user") > "" && Session::get("logged_in");
		}
	}

	/**
	 * Run and output the app.
	 */
	public function execute(): void
	{
		// Parameter "mod" is the mandatory module name
		$modName = RQ::GET('mod') ?: Config::get("app_settings", "default_module");

		// Parameter "render" is optional
		$render = RQ::GET('render') ?: "html";

		// With parameter render="json" a module is rendered as an object with metadata and its own slice template only.
		if ($render == "json") {
			Config::init($modName);
			$appHtml = Module::create($modName)->build();
		} else {
			$html = $this->build();
			$debugPanel = Tools::renderDebugPanel();
			$appHtml = Template::html($html)->parse(["</body>" => "$debugPanel\n</body>"]);
		}

		die($appHtml);
	}

	/**
	 * Creates an object of a module.
	 * @param mixed $modName
	 * @param mixed $force
	 * @return mixed
	 */
	public static function create(string $modName, string $force = ""): mixed
	{
		$modFile = dirname(__DIR__, 1) . "/modules/$modName/controller/$modName.php";

		if (is_file($modFile)) {
			$modConfig = Tools::loadIniFile($modName);
			$protected = isset($modConfig["mod_settings"]["protected"]) ? intval($modConfig["mod_settings"]["protected"]) : 0;
			if ($protected && !Module::checkLogin())
				return new AccessDeniedMod($modName, $force);

			$className = Tools::upperCamelCase($modName);

			require_once $modFile;
			$module = new $className();
			$module->injectServices();
			$module->setRender($force);
			if (RQ::GET('task')) {
				$task = RQ::GET('task');
				die($module->$task());
			}
			return $module;
		} else {
			return new ErrorMod($modName, $force);
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

	protected function setRender(string $render = "") : void
	{
		$this->render = $render;
	}

	protected function getRender() : string
	{
		return $this->render;
	}

	abstract protected function build(): string;

	protected function getTemplate($fileName = ""): Template
	{
		$fname = $fileName ? $fileName : $this->modName . ".html";
		return Template::load($this->className, $fname);
	}

	protected function render(string $html = ""): string
	{
		if (!RQ::GET('render') || RQ::GET('render') == 'html' || $this->getRender() == "html")
			return $html;
		else
			return $this->renderJSON($html, "content");
	}

	/**
	 * Returns meta data of a page as a JSON Object.
	 * @param string $html HTML content if no $dataMod is set as target.
	 * @param string $dataMod Name of the module that should be replaced with $html.
	 * @param array  $payload Can be what ever you require. 
	 * @param string $content HTML you want to appear in the content section.
	 * @return string JSON Object
	 */
	protected function renderJSON(string $html = "", string $dataMod = "", array $payload = [], string $content = ""): string
	{
		$toJson = [
			"title" => Config::get("page_settings", "title"),
			"module" => $this->modName,
			"payload" => $payload,
			"segment" => [],
			"content" => $content
		];

		if ($dataMod && $html) {
			$toJson['segment'] = ["dataMod" => $dataMod, "html" => $html];
		} else if ((!$dataMod && $html && !$content)) {
			$toJson['content'] = $html;
		}

		return json_encode($toJson);
	}

	protected function setLogin(string $un) : void
	{
		Session::set("logged_in", true);
        Session::set("user", $un);
	}

	public static function checkLogin() : bool
	{
		return self::$isLoggedIn;
	}

	protected function setLogout() : void
	{
		Session::set("logged_in", false);
        Session::set(Session::get("user"), null);
	}

	public function __call(string $method, mixed $args): string
	{
		return Tools::errorMessage("Task $method() not defined!");
	}
}


class ErrorMod {

	public function __construct(
		private $modName, 
		private $force = ""
	) {}

	public function build(): string
	{
		$html = Tools::errorMessage("Module '$this->modName' not found!");

		if (!RQ::GET('render') || RQ::GET('render') == 'html' || $this->force == 'html') {
			return $html;
		} else {
			return json_encode([
				"title" => "Error!",
				"module" => $this->modName,
				"payload" => [],
				"segment" => [],
				"content" => $html
			]);
		}
	}
}


class AccessDeniedMod {
	public function __construct(
		private $modName, 
		private $force = ""
	) {}

	public function build()
	{
		$html = Tools::errorMessage("You are not permitted to view this content! Please log in or sign up.");

		if (!RQ::GET('render') || RQ::GET('render') == 'html' || $this->force == 'html') {
			return $html;
		} else {
			return json_encode([
				"title" => "Access denied!",
				"module" => $this->modName,
				"payload" => [],
				"segment" => [],
				"content" => $html
			]);
		}
	}
}