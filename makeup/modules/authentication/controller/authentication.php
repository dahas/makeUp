<?php

use makeUp\lib\Config;
use makeUp\lib\Lang;
use makeUp\lib\Module;
use makeUp\lib\RQ;
use makeUp\lib\Tools;
use makeUp\lib\Session;


class Authentication extends Module {
    public function __construct()
    {
        parent::__construct();
    }


    protected function build(string $variant = ""): string
    {
        $html = match ($variant) {
            default => $this->buildRegistrationForm(),
            "form" => Module::checkLogin() ? $this->buildLogoutForm() : $this->buildLoginForm()
        };
        return $this->render($html);
    }


    private function buildLoginForm(): string
    {
        return $this->getTemplate("authentication.login.html")->parse([
            "[[FORM_ACTION]]" => Tools::linkBuilder($this->modName, "signin"),
            "[[REGISTER_LINK]]" => Tools::linkBuilder("authentication"),
            "[[TOKEN]]" => Tools::createFormToken()
        ]);
    }


    private function buildLogoutForm(): string
    {
        return $this->getTemplate("authentication.logout.html")->parse([
            "[[FORM_ACTION]]" => Tools::linkBuilder($this->modName, "signout"),
            "[[TOKEN]]" => Tools::createFormToken()
        ]);
    }


    private function buildRegistrationForm(): string
    {
        if (!Module::checkLogin()) {
            $token = Tools::createFormToken();

            $html = $this->getTemplate("authentication.register.html")->parse([
                "[[FORM_ACTION]]" => Tools::linkBuilder($this->modName, "register"),
                "[[TOKEN]]" => $token
            ]);
        } else {
            $html = $this->getTemplate("authentication.signup.html")->parse([
                "[[WELCOME_MSG]]" => sprintf(Lang::get("welcome"), Session::get('user'))
            ]);
        }
        return $html;
    }


    public function signin()
    {
        $segments = [];
        if ($this->authorized(RQ::POST('login_token'), RQ::POST('username'), RQ::POST('password'))) {
            $this->setLogin(RQ::POST('username'));
            $toast = ["success", Lang::get('signed_in')];
            $navigation = Module::create("navigation", "html")->build();
            $content = Module::create(Session::get("route"), "html")->build();
            array_push($segments, ["dataMod" => "authentication", "html" => $this->buildLogoutForm()]);
            array_push($segments, ["dataMod" => "navigation", "html" => $navigation]);
            array_push($segments, ["dataMod" => "content", "html" => $content]);
        } else {
            $toast = ["error", Lang::get('login_failed')];
        }

        return json_encode([
            "title" => Config::get("page_settings", "title"),
            "module" => "authentication",
            "toast" => $toast,
            "segments" => $segments
        ]);
    }


    public function signout()
    {
        $segments = [];
        $this->setLogout();
        $navigation = Module::create("navigation", "html")->build();
        $route = Session::get("route");
        $routeMod = Module::create($route, "html");
        $content = !$routeMod->accessDenied ? $routeMod->build() : Module::create("index", "html")->build();
        array_push($segments, ["dataMod" => "authentication", "html" => $this->buildLoginForm()]);
        array_push($segments, ["dataMod" => "navigation", "html" => $navigation]);
        array_push($segments, ["dataMod" => "content", "html" => $content]);
        return json_encode([
            "title" => Config::get("page_settings", "title"),
            "module" => "authentication",
            "toast" => ["success", Lang::get('signed_out')],
            "segments" => $segments
        ]);
    }


    public function authorized(string $token, string $un, string $pw): bool
    {
        $docRoot = dirname(__DIR__, 3);
        $file = fopen($docRoot . "/users.txt", "r");
        $userData = $this->userExists($file, $un);

        if (!$userData)
            return false;

        $username = $userData[0];
        $hash = $userData[1];
        $validPw = password_verify($pw, $hash);
        fclose($file);
        return Tools::checkFormToken($token) && $username === $un && $validPw;
    }


    public function register()
    {
        $docRoot = dirname(__DIR__, 3);
        $file = fopen($docRoot . "/users.txt", "a+");

        if (!Module::checkLogin() && !$this->userExists($file, RQ::POST('username')) && Tools::checkFormToken(RQ::POST('reg_token')) && RQ::POST('username') && RQ::POST('password')) {
            $userdata = RQ::POST('username') . ":" . password_hash(RQ::POST('password'), PASSWORD_BCRYPT) . ":END";
            fwrite($file, $userdata . PHP_EOL);
            Session::set("logged_in", true);
            Session::set("user", RQ::POST('username'));
            $response = "success";
            $m["[[WELCOME_MSG]]"] = sprintf(Lang::get("welcome"), Session::get('user'));
            $content = $this->getTemplate("authentication.signup.html")->parse($m);
            $html = $this->buildLogoutForm();
        } else {
            $response = "error";
            $content = "";
            $html = $this->buildLoginForm();
        }
        fclose($file);
        return json_encode([
            "title" => Config::get("page_settings", "title"),
            "module" => "authentication",
            "toast" => [$response, Lang::get($response)],
            "segments" => [
                ["dataMod" => "authentication", "html" => $html],
                ["dataMod" => "content", "html" => $content]
            ]
        ]);
    }


    private function userExists($file, string $username): array |false
    {
        if ($file) {
            while (($line = fgets($file, 4096)) !== FALSE) {
                $dataArr = explode(":", $line);
                if ($dataArr[0] == $username) {
                    return $dataArr;
                }
            }
        }
        return false;
    }

}