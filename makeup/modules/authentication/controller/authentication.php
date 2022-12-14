<?php

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
            "form" => $this->buildSignInOutForm()
        };
        return $this->render($html);
    }


    private function buildSignInOutForm(): string
    {
        $template = "authentication.login.html";
        $token = Tools::createFormToken();

        if (Session::get("logged_in")) {
            $html = $this->getTemplate($template)->getSlice("{{SIGNOUT}}")->parse([
                "[[FORM_ACTION]]" => Tools::linkBuilder($this->modName, "signout"),
                "[[TOKEN]]" => $token
            ]);
        } else {
            $html = $this->getTemplate($template)->getSlice("{{SIGNIN}}")->parse([
                "[[FORM_ACTION]]" => Tools::linkBuilder($this->modName, "signin"),
                "[[REGISTER_LINK]]" => Tools::linkBuilder("authentication"),
                "[[TOKEN]]" => $token
            ]);
        }

        return $html;
    }


    private function buildRegistrationForm(): string
    {
        if (!Session::get('logged_in')) {
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
        if ($this->authorized(RQ::POST('token'), RQ::POST('username'), RQ::POST('password'))) {
            Session::set("logged_in", true);
            Session::set("user", RQ::POST('username'));
            $m["[[WELCOME_MSG]]"] = sprintf(Lang::get("welcome"), Session::get('user'));
            return $this->renderJSON("authentication", $this->buildSignInOutForm(), ["toast" => ["success", Lang::get('signed_in')]]);
        }
        return $this->renderJSON("authentication", $this->buildSignInOutForm(), ["toast" => ["error", Lang::get('login_failed')]]);
    }


    public function signout()
    {
        Session::set("logged_in", false); // Simulate logout
        Session::set("user", null);
        return $this->renderJSON("authentication", $this->buildSignInOutForm(), ["toast" => ["success", Lang::get('signed_out')]]);
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

        if (!Session::get("logged_in") && !$this->userExists($file, RQ::POST('username')) && Tools::checkFormToken(RQ::POST('token')) && RQ::POST('username') && RQ::POST('password')) {
            $userdata = RQ::POST('username') . ":" . password_hash(RQ::POST('password'), PASSWORD_BCRYPT) . ":END";
            fwrite($file, $userdata . PHP_EOL);
            Session::set("logged_in", true);
            Session::set("user", RQ::POST('username'));
            $response = "success";
            $m["[[WELCOME_MSG]]"] = sprintf(Lang::get("welcome"), Session::get('user'));
            $content = $this->getTemplate("authentication.signup.html")->parse($m);
        } else {
            $response = "error";
            $content = "";
        }
        fclose($file);
        return $this->renderJSON("authentication", $this->buildSignInOutForm(), ["toast" => [$response, Lang::get($response)]], $content);
    }


    private function userExists($file, string $username) : array|false
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