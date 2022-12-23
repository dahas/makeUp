<?php declare(strict_types=1);

namespace makeUp\lib;

use ReflectionClass;


abstract class Module {
	private static array $arguments = [];
	protected $config = array();
	private $className = "";
	protected $modName = "";
	protected $render = "";
	protected $protected = 0;
	protected $history_caching = true;
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
		$this->procArguments(func_get_args());

		$params = self::getParameters();
        $class = ucfirst(self::getModName());
		$modName = $class ?: Config::get("app_settings", "default_module");

		$render = isset($params['render']) ? $params['render'] : "html";

		if (!isset($params['task'])) {
			$task = "build";
			Session::set("route", $modName);
		} else {
			$task = $params['task'];
		}

		if ($render == "json" || $task != "build") {
			Config::init($modName);
			$appHtml = Module::create($modName, $render)->$task();
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
	 * @param mixed $render
	 * @return mixed
	 */
	public static function create(string $modName, string $render = "html"): mixed
	{
		// $params = Module::getParameters();
		$modFile = dirname(__DIR__, 1) . "/modules/$modName/controller/$modName.php";

		if (is_file($modFile)) {
			$modConfig = Tools::loadIniFile($modName);
			$protected = isset($modConfig["mod_settings"]["protected"]) ? intval($modConfig["mod_settings"]["protected"]) : 0;
			if ($protected && !Module::checkLogin())
				return new AccessDeniedMod($modName, $render);

			$className = Tools::upperCamelCase($modName);

			require_once $modFile;
			$module = new $className();
			$module->injectServices();
			$module->setRender($render);
			$module->setProtected($protected);
			if ($protected)
				$module->setHistCaching(false);
			if (RQ::GET('task') && $render != "html") {
				$task = RQ::GET('task');
				die($module->$task());
			}
			return $module;
		} else {
			return new ErrorMod($modName, $render);
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

	protected function setRender(string $render = ""): void
	{
		$this->render = $render;
	}

	protected function getRender(): string
	{
		return $this->render;
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
			"title" => Config::get("page_settings", "title"),
			"caching" => $this->getHistoryCaching(),
			"module" => $this->modName,
			"content" => $html
		]);
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
    public static function getModName(): string
    {
        return self::$arguments['modules'][0];
    }

    /**
     * Access GET and POST vars in Modules.
     * @return array
     */
    public static function getParameters(): array
    {
        return self::$arguments['parameters'];
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
		return Tools::errorMessage("Task $method() not defined!");
	}
}


class ErrorMod {

	public function __construct(
		private $modName,
		private $force = ""
	)
	{
	}

	public function build(): string
	{
		$html = Tools::errorMessage("Module '$this->modName' not found!");

		if (!RQ::GET('render') || RQ::GET('render') == 'html' || $this->force == 'html') {
			return $html;
		} else {
			return json_encode([
				"title" => "Error!",
				"caching" => true,
				"module" => $this->modName,
				"content" => $html
			]);
		}
	}

	public function __call(string $name, array $arguments ) : void
	{

	}
}


class AccessDeniedMod {

	private $protected = 1;

	public function __construct(
		private $modName,
		private $force = ""
	)
	{
	}

	public function build()
	{
		$html = Tools::errorMessage("You are not permitted to view this content! Please log in or sign up.");

		if (!RQ::GET('render') || RQ::GET('render') == 'html' || $this->force == 'html') {
			return $html;
		} else {
			return json_encode([
				"title" => "Access denied!",
				"caching" => false,
				"module" => $this->modName,
				"content" => $html
			]);
		}
	}

	public function isProtected(): int
	{
		return $this->protected;
	}
}