<?php declare(strict_types=1);

namespace makeUp\src;

use makeUp\src\interfaces\HttpRequest;


class Request implements HttpRequest {

    private array $route;
    private array $parameters;

    public function __construct()
    {
        if (isset($_SERVER['REQUEST_URI'])) {
            $uri = parse_url($_SERVER['REQUEST_URI']);
            $this->route = explode("/", substr($uri['path'], 1));
    
            $query = [];
            if (isset($uri['query']) && $uri['query']) {
                parse_str($uri['query'], $query);
            }
            $params = array_merge($_POST, $query);
    
            $this->parameters = $this->parseRequest($params);
        }

        $_GET = null;
        $_POST = null;
        $_REQUEST = null;
    }

    public function isXHR(): bool
    {
        return (isset($_SERVER['HTTP_X_MAKEUP_AJAX']) && $_SERVER['HTTP_X_MAKEUP_AJAX'] == 1)
            || isset($_SERVER['HTTP_X_REQUESTED_WITH']);
    }

    public function getMethod(): string
    {
        return $_SERVER['REQUEST_METHOD'] ?? "";
    }

    public function issetRouteHeader(): bool 
    {
        return isset($_SERVER['HTTP_X_MAKEUP_ROUTE']);
    }

    public function getRouteHeader(): string 
    {
        if (isset($_SERVER['HTTP_X_MAKEUP_ROUTE'])) {
            return substr($_SERVER['HTTP_X_MAKEUP_ROUTE'], 1);
        }
        return "";
    }

    public function getModule(): string
    {
        if (isset($this->route[0]) && $this->route[0]) {
            return $this->route[0];
        }
        return Config::get("app_settings", "default_module");
    }

    public function getTask(): string
    {
        if (isset($this->route[1])) {
            return $this->route[1];
        }
        return "build";
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function getParameter(string $name): string
    {
        if(isset($this->parameters[$name])) {
            return $this->parameters[$name];
        }
        return "";
    }

    /**
     * Sanitizing GET/POST vars array.
     * @param array $query
     * @return array
     */
    public function parseRequest(array $request): array
    {
        return array_map('self::filterInput', $request);
    }


    public static function filterInput(mixed $var): string
    {
        return htmlspecialchars(string: $var, encoding: Config::get("metatags", "charset"));
    }
}