<?php declare(strict_types=1);

namespace makeUp\lib;

use makeUp\lib\traits\Utils;

class Router {

    use Utils;

    private array $handler;
    private const GET = "GET";
    private const POST = "POST";


    /**
     * **get()**
     * Execute callable function when the specific path is set as a GET request.
     * @param string $path
     * @param callable|array $callback
     * @return Router
     */
    public function get(string $path, callable |array $callback): Router
    {
        $this->addHandler(self::GET, $path, $callback);
        return $this;
    }


    /**
     * **post()**
     * Execute callable function when the specific path is set as a POST request.
     * @param string $path
     * @param callable|array $callback
     * @return Router
     */
    public function post(string $path, callable |array $callback): Router
    {
        $this->addHandler(self::POST, $path, $callback);
        return $this;
    }


    private function addHandler(string $method, string $path, callable |array $callback): void
    {
        $this->handler = [
            "method" => $method,
            "path" => $path,
            "callback" => $callback
        ];
    }


    /**
     * Run the callback handlers
     * @return string HTML
     */
    public function run(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI']);
        $path = $uri['path'];
        $query = [];
        if (isset($uri['query']) && $uri['query']) {
            parse_str($uri['query'], $query);
            $query = $this->parseQuery($query); // <-- GET vars are filtered and sanitized
        }
        $formData = $this->parseFormData($_POST); // <-- POST vars are filtered and sanitized

        $modules = explode("/", $path);
        array_shift($modules);
        if (sizeof($modules) == 1 && !$modules[0])
            $modules = [];

        $callback = [new $this->handler["callback"][0], $this->handler["callback"][1]];

        call_user_func_array($callback, [[
            "method" => $method,
            "modules" => $modules,
            "parameters" => array_merge($query, $formData)
        ]]);
    }
}