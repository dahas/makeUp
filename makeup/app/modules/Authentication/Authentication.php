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


    public function buildLoginForm(): string
    {
        $html = $this->getTemplate("Authentication.login.html")->parse([
            "[[FORM_ACTION]]" => Utils::linkBuilder($this->modName, "signin"),
            "[[REGISTER_LINK]]" => Utils::linkBuilder("Authentication"),
            "[[TOKEN]]" => Utils::createFormToken("auth")
        ]);
        return $this->render($html);
    }


    public function buildLogoutForm(): string
    {
        $html = $this->getTemplate("Authentication.logout.html")->parse([
            "[[FORM_ACTION]]" => Utils::linkBuilder($this->modName, "signout")
        ]);
        return $this->render($html);
    }


    public function buildRegistrationForm(): string
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
        $rq = $this->requestData();
        if ($this->authorized($rq['login_token'], $rq['username'], $rq['password'])) {
            $this->auth(true);
            $toast = ["success", Lang::get('signed_in')];
            $context = $this->routeMod();
        } else {
            $toast = ["error", Lang::get('login_failed')];
            $context = "";
        }

        return json_encode([
            "title" => Config::get("page_settings", "title"),
            "module" => "Authentication",
            "toast" => $toast,
            "context" => $context
        ]);
    }


    public function signout()
    {
        $this->auth(false);
        $routeMod = Module::create($this->routeMod());
        $context = !$routeMod->isProtected() ? $this->routeMod() : "Home";
        return json_encode([
            "title" => Config::get("page_settings", "title"),
            "module" => "Authentication",
            "toast" => ["success", Lang::get('signed_out')],
            "context" => $context
        ]);
    }


    public function register()
    {
        $params = $this->requestData();
        $docRoot = dirname(__DIR__, 3);
        $file = fopen($docRoot . "/users.txt", "a+");

        if (!Module::checkLogin() && !$this->userExists($file, $params['username']) &&
            Utils::checkFormToken("reg", $params['reg_token']) && $params['username'] && $params['password']) {
            $userdata = $params['username'] . ":" . password_hash($params['password'], PASSWORD_BCRYPT) . ":END";
            fwrite($file, $userdata . PHP_EOL);
            Session::set("logged_in", true);
            Session::set("user", $params['username']);
            $response = "success";
            $context = "Authentication";
        } else {
            $response = "error";
            $context = "";
        }
        fclose($file); 

        return json_encode([
            "title" => Config::get("page_settings", "title"),
            "module" => "Authentication",
            "toast" => [$response, Lang::get($response)],
            "context" => $context
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