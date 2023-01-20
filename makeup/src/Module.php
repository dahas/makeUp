<?php declare(strict_types=1);

namespace makeUp\src;

use makeUp\lib\Template;
use ReflectionClass;


abstract class Module {

	protected $config = array();
	protected $modName = "";
	protected $render = "";
	protected $dataMod = "App";
	protected $protected = 0;
	protected static $isLoggedIn = false;


	public function __construct()
	{
		$modNsArr = explode("\\", $this::class);
		$this->modName = array_pop($modNsArr);

		Session::start();

		// Order matters!
		Config::init(self::name());
		Lang::init();
		if (Config::get("cookie", "name"))
			Cookie::read(Config::get("cookie", "name"));

		self::$isLoggedIn = Session::get("logged_in") && Session::get("logged_in") === true;
	}

	abstract protected function build(Request $request): string;


	public function handle(Request $request, Response $response): void
	{
		$modName = $request->getModule();
		$task = $request->getTask();

		if (!Session::get("routeMod")) {
			Session::set("routeMod", $modName);
		}

		if ($request->issetRouteHeader()) {
			if ($request->getRouteHeader()) {
				Session::set("routeMod", $request->getRouteHeader());
			} else {
				Session::set("routeMod", "Home");
			}
		}

		$render = $request->isXHR() ? "json" : "html";

		if ($render == "json" || $task != "build") { // Create only the Module
			$appHtml = self::create($modName, $render)->$task($request);
		} else { // Create the whole App
			$appHtml = $this->build($request);
		}

		$response->setStatus("200 OK");
		$response->addHeader("Auth", $this->checkLogin() ? "1" : "0");
		$response->write($appHtml);
		$response->flush();
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
		$modFile = dirname(__DIR__, 1) . "/app/modules/$modName/$modName.php";

		if (is_file($modFile)) {
			$modConfig = Utils::loadIniFile($modName);
			$protected = isset($modConfig["mod_settings"]["protected"]) ? intval($modConfig["mod_settings"]["protected"]) : 0;
			if ($protected && !self::checkLogin()) {
				$module = new AccessDenied();
			} else {
				require_once $modFile;
				$module = new $modName();
				$module->injectServices();
			}
			$module->setRender($render);
			$module->setProtected($protected);
			if ($useDataMod)
				$module->setDataMod($modName);
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