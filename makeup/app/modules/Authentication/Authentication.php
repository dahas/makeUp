<?php

use makeUp\lib\Config;
use makeUp\lib\Lang;
use makeUp\lib\Module;
use makeUp\lib\Utils;
use makeUp\lib\Session;


class Authentication extends Module {

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
        return $this->getTemplate("Authentication.login.html")->parse([
            "[[FORM_ACTION]]" => Utils::linkBuilder($this->modName, "signin"),
            "[[REGISTER_LINK]]" => Utils::linkBuilder("Authentication"),
            "[[TOKEN]]" => Utils::createFormToken("auth")
        ]);
    }


    private function buildLogoutForm(): string
    {
        return $this->getTemplate("Authentication.logout.html")->parse([
            "[[FORM_ACTION]]" => Utils::linkBuilder($this->modName, "signout")
        ]);
    }


    private function buildRegistrationForm(): string
    {
        if (!Module::checkLogin()) {
            $token = Utils::createFormToken("reg");

            $html = $this->getTemplate("Authentication.register.html")->parse([
                "[[FORM_ACTION]]" => Utils::linkBuilder($this->modName, "register"),
                "[[TOKEN]]" => $token
            ]);
        } else {
            $html = $this->getTemplate("Authentication.signup.html")->parse([
                "[[WELCOME_MSG]]" => sprintf(Lang::get("welcome"), Session::get('user'))
            ]);
        }
        return $html;
    }


    public function signin()
    {
        $params = Module::getParameters();
        $segments = [];
        if ($this->authorized($params['login_token'], $params['username'], $params['password'])) {
            $this->setLogin($params['username']);
            $toast = ["success", Lang::get('signed_in')];
            $Navigation = Module::create("Navigation")->build();
            $content = Module::create(Session::get("route"))->build();
            array_push($segments, ["dataMod" => "Authentication", "html" => $this->buildLogoutForm()]);
            array_push($segments, ["dataMod" => "Navigation", "html" => $Navigation]);
            array_push($segments, ["dataMod" => "App", "html" => $content]);
        } else {
            $toast = ["error", Lang::get('login_failed')];
        }

        return json_encode([
            "title" => Config::get("page_settings", "title"),
            "module" => "Authentication",
            "toast" => $toast,
            "segments" => $segments
        ]);
    }


    public function signout()
    {
        $segments = [];
        $this->setLogout();
        $Navigation = Module::create("Navigation")->build();
        $routeMod = Module::create(Session::get("route"));
        $content = !$routeMod->isProtected() ? $routeMod->build() : Module::create("Home")->build();
        array_push($segments, ["dataMod" => "Authentication", "html" => $this->buildLoginForm()]);
        array_push($segments, ["dataMod" => "Navigation", "html" => $Navigation]);
        array_push($segments, ["dataMod" => "App", "html" => $content]);
        return json_encode([
            "title" => Config::get("page_settings", "title"),
            "module" => "Authentication",
            "toast" => ["success", Lang::get('signed_out')],
            "segments" => $segments
        ]);
    }


    public function register()
    {
        $params = Module::getParameters();
        $segments = [];
        $docRoot = dirname(__DIR__, 3);
        $file = fopen($docRoot . "/users.txt", "a+");

        if (!Module::checkLogin() && !$this->userExists($file, $params['username']) &&
            Utils::checkFormToken("reg", $params['reg_token']) && $params['username'] && $params['password']) {
            $userdata = $params['username'] . ":" . password_hash($params['password'], PASSWORD_BCRYPT) . ":END";
            fwrite($file, $userdata . PHP_EOL);
            Session::set("logged_in", true);
            Session::set("user", $params['username']);
            $response = "success";
            $m["[[WELCOME_MSG]]"] = sprintf(Lang::get("welcome"), Session::get('user'));
            $Navigation = Module::create("Navigation")->build();
            array_push($segments, ["dataMod" => "Authentication", "html" => $this->buildLogoutForm()]);
            array_push($segments, ["dataMod" => "Navigation", "html" => $Navigation]);
            array_push($segments, ["dataMod" => "App", "html" => $this->getTemplate("Authentication.signup.html")->parse($m)]);
        } else {
            $response = "error";
            array_push($segments, ["dataMod" => "Authentication", "html" => $this->buildLoginForm()]);
        }
        fclose($file); 

        return json_encode([
            "title" => Config::get("page_settings", "title"),
            "module" => "Authentication",
            "toast" => [$response, Lang::get($response)],
            "segments" => $segments
        ]);
    }


    public function authorized(string $token, string $un, string $pw): bool
    {
        $docRoot = dirname(__DIR__, 3);
        $file = @fopen($docRoot . "/users.txt", "r");
        if (!$file)
            return false;

        $userData = $this->userExists($file, $un);

        if (!$userData)
            return false;

        $username = $userData[0];
        $hash = $userData[1];
        $validPw = password_verify($pw, $hash);
        fclose($file);
        return Utils::checkFormToken("auth", $token) && $username === $un && $validPw;
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