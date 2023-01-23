<?php declare(strict_types=1);

namespace makeUp\src;

use makeUp\lib\Auth;
use ReflectionClass;


abstract class Module {

	protected array $config;
	protected string $name;
	protected bool $isXHR = false;
	protected string $dataMod = "App";
	protected int $protected = 0;


	public function __construct()
	{
		// Order matters!
		Session::start();
		Config::init();
		Lang::init();
		if (Config::get("cookie", "name"))
			Cookie::read(Config::get("cookie", "name"));
	}

	abstract protected function build(Request $request): string;


	public function handle(Request $request): void
	{
		$response = new Response();

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

		if ($request->isXHR() || $task != "build") { // Create only the Module
			$module = Module::create($request->getModule(), $request->isXHR());
			$appHtml = $module->$task($request);
		} else { // Create the whole App
			$appHtml = $this->build($request);
		}

		$response->setStatus("200 OK");
		$response->addHeader("Auth", Auth::check() ? "1" : "0");
		$response->write($appHtml);
		$response->flush();
	}


	/**
	 * Creates an instance of a module.
	 * @param string $modName The name of the Module.
	 * @param bool $isXHR Whether the request is asynchronous or not.
	 * @param bool $useDataMod Render response into the related data-mod tag of this Module. Otherwise data-mod="App" is used.
	 * @return mixed
	 */
	public static function create(string $modName, bool $isXHR = false, bool $useDataMod = false): mixed
	{
		$modFile = dirname(__DIR__, 1) . "/app/modules/$modName/$modName.php";

		if (is_file($modFile)) {
			$modConfig = Utils::loadIniFile($modName);
			$protected = isset($modConfig["mod_settings"]["protected"]) ? intval($modConfig["mod_settings"]["protected"]) : 0;
			if ($protected && !Auth::check()) {
				$module = new AccessDenied();
			} else {
				require_once $modFile;
				$module = new $modName();
				$module->injectServices();
			}
			$module->setXHR($isXHR);
			$module->setName($modName);
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
		if ($this->isXHR())
			return $this->renderJSON($html);
		else
			return $html;
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
			"module" => $this->getName(),
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


	protected function setXHR(bool $xhr = false): void
	{
		$this->isXHR = $xhr;
	}

	protected function isXHR(): bool
	{
		return $this->isXHR;
	}


	public function setName(string $name): void
	{
		$this->name = $name;
	}

	public function getName(): string
	{
		return $this->name;
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


	protected function routeMod(): string
	{
		return Session::get("routeMod");
	}


	public function __call(string $method, mixed $args): string
	{
		return Utils::errorMessage("Task $method() not defined!");
	}
}