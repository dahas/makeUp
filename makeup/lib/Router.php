<?php declare(strict_types=1);

namespace makeUp\lib;

class Router {

    private array $handler;
    private const GET = "GET";
    private const POST = "POST";


    public function __construct()
    {
        Session::start();
    }


    /**
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


    /**
     * Add callback to handler.
     * @param string $method
     * @param string $path
     * @param callable|array $callback
     * @return void
     */
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
     */
    public function run(): void
    {
        // Debugging:
        if (isset($_SERVER['argc']) && $_SERVER['argc'] > 1) {
            $method = $_SERVER['argv'][4];
            $uri = parse_url($_SERVER['argv'][2]);
            parse_str($_SERVER['argv'][6], $formData);
        } else {
            $method = $_SERVER['REQUEST_METHOD'];
            $uri = parse_url($_SERVER['REQUEST_URI']);
            $formData = $this->parseFormData($_POST); // <-- POST vars are filtered and sanitized
        }

        $path = $uri['path'];
        $query = [];
        if (isset($uri['query']) && $uri['query']) {
            parse_str($uri['query'], $query);
            $query = $this->parseQuery($query); // <-- GET vars are filtered and sanitized
        }

        $routeArr = explode("/", $path);
        array_shift($routeArr);
        if (sizeof($routeArr) == 1 && !$routeArr[0])
            $routeArr = [];

        $callback = [new $this->handler["callback"][0], $this->handler["callback"][1]];

        call_user_func_array($callback, [[
            "method" => $method,
            "module" => $routeArr,
            "parameters" => array_merge($query, $formData)
        ]]);
    }


    /**
     * Sanitizing GET variables.
     * @param array $query
     * @return array
     */
    public function parseQuery(array $query): array
    {
        return array_map('self::filterInput', $query);
    }


    /**
     * Sanitizing POST variables.
     * @param array $formData
     * @return array
     */
    public function parseFormData(array $formData): array
    {
        return array_map('self::filterInput', $formData);
    }


    /**
     * Applies Filter.
     * @param mixed $input
     * @return string
     */
    private static function filterInput($input): string
    {
        return htmlspecialchars(string: $input, encoding: Config::get("metatags", "charset"));
    }

}