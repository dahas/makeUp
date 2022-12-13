<?php

use makeUp\lib\Lang;
use makeUp\lib\Module;
use makeUp\lib\RQ;
use makeUp\lib\Config;
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
            default => $this->buildRegister(),
            "response" => $this->buildResponse(),
            "fail" => $this->buildFail(),
            "form" => $this->buildForm()
        };
        return $this->render($html);
    }

    function renderJSON(string $html = ""): string
    {
        return json_encode([
            "title" => Config::get("page_settings", "title"),
            "module" => $this->modName,
            "segments" => [
                ["target" => 'content', "html" => $html],
                ["target" => 'authentication', "html" => $this->buildForm()]
            ]
        ]);
    }


    private function buildForm(): string
    {
        $template = "authentication.form.html";
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


    private function buildResponse(): string
    {
        $template = "authentication.response.html";

        if (Session::get("logged_in")) {
            $m["[[SIGNED_IN]]"] = Lang::get("signed_in");
            $response = $this->getTemplate($template)->getSlice("{{SIGNOUT}}")->parse($m);
        } else {
            $m["[[SIGNED_OUT]]"] = Lang::get("signed_out");
            $response = $this->getTemplate($template)->getSlice("{{SIGNIN}}")->parse($m);
        }

        return $this->getTemplate()->parse(["[[FORM]]" => $response]);
    }


    private function buildFail(): string
    {
        $m["[[LOGIN_FAILED]]"] = Lang::get("login_failed");
        $fail = $this->getTemplate("authentication.fail.html")->parse($m);

        return $this->getTemplate()->parse(["[[FORM]]" => $fail]);
    }


    private function buildRegister(): string
    {
        $token = Tools::createFormToken();

        $html = $this->getTemplate("authentication.register.html")->parse([
            "[[FORM_ACTION]]" => Tools::linkBuilder($this->modName, "signup"),
            "[[TOKEN]]" => $token
        ]);
        return $html;
    }


    private function buildSignup(string $resp): string
    {
        $template = "authentication.signup.html";

        if ($resp == "success") {
            $m["[[SUCCESS_MSG]]"] = Lang::get("success");
            $html = $this->getTemplate($template)->getSlice("{{SUCCESS}}")->parse($m);
        } else {
            $m["[[ERROR_MSG]]"] = Lang::get("error");
            $m["[[BACK_MSG]]"] = Lang::get("back");
            ;
            $html = $this->getTemplate($template)->getSlice("{{ERROR}}")->parse($m);
        }
        return $this->render($html);
    }


    public function signin()
    {
        if ($this->authenticate(RQ::POST('token'), RQ::POST('username'), RQ::POST('password'))) {
            Session::set("logged_in", true);
            return $this->build("response");
        }
        return $this->build("fail");
    }


    public function signout()
    {
        Session::set("logged_in", false); // Simulate logout
        return $this->build("response");
    }


    public function authenticate(string $token, string $un, string $pw): bool
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
        return $this->build("register");
    }


    public function signup()
    {
        $docRoot = dirname(__DIR__, 3);
        $file = fopen($docRoot . "/users.txt", "a+");

        if (!$this->userExists($file, RQ::POST('username')) && Tools::checkFormToken(RQ::POST('token')) && RQ::POST('username') && RQ::POST('password')) {
            $userdata = RQ::POST('username') . ":" . password_hash(RQ::POST('password'), PASSWORD_BCRYPT) . ":END";
            fwrite($file, $userdata . PHP_EOL);
            Session::set("logged_in", true);
            $response = "success";
        } else {
            $response = "error";
        }
        fclose($file);
        return $this->buildSignup($response);
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