<?php

use makeUp\lib\Auth;
use makeUp\lib\Template;
use makeUp\src\Config;
use makeUp\src\Lang;
use makeUp\src\Module;
use makeUp\src\Request;
use makeUp\src\Utils;
use makeUp\src\Session;


class Authentication extends Module {

    private Auth $auth;

    public function __construct()
    {
        $this->auth = new Auth();
    }

    protected function build(Request $request, string $variant = ""): string
    {
        $html = match ($variant) {
            default => $this->buildRegistrationForm(),
            "form" => $this->auth->check() ? $this->buildLogoutForm() : $this->buildLoginForm()
        };
        return $this->render($html);
    }


    public function buildLoginForm(): string
    {
        $html = Template::load("Authentication", "Authentication.login.html")->parse([
            "[[FORM_ACTION]]" => Utils::linkBuilder("Authentication", "signin"),
            "[[TOKEN]]" => $this->auth->createFormToken("auth")
        ]);
        return $this->render($html);
    }


    public function buildLogoutForm(): string
    {
        $html = Template::load("Authentication", "Authentication.logout.html")->parse([
            "[[FORM_ACTION]]" => Utils::linkBuilder("Authentication", "signout")
        ]);
        return $this->render($html);
    }


    public function buildRegistrationForm(): string
    {
        if (!$this->auth->check()) {
            $token = $this->auth->createFormToken("reg");

            $html = Template::load("Authentication", "Authentication.register.html")->parse([
                "[[FORM_ACTION]]" => Utils::linkBuilder("Authentication", "register"),
                "[[TOKEN]]" => $token
            ]);
        } else {
            $html = Template::load("Authentication", "Authentication.signup.html")->parse([
                "[[WELCOME_MSG]]" => sprintf(Lang::get("welcome"), Session::get('user'))
            ]);
        }
        return $html;
    }


    public function signin(Request $request)
    {
        $token = $request->getParameter("login_token");
        $username = $request->getParameter("username");
        $password = $request->getParameter("password");

        $authorized = false;

        if ($this->auth->checkFormToken("auth", $token)) {
            $authorized = $this->auth->authorize($username, $password);
            if ($authorized) {
                $toast = ["success", Lang::get('signed_in')];
                $context = $this->routeMod();
            } else {
                $toast = ["error", Lang::get('login_failed')];
                $context = "";
            }
        } else {
            $toast = ["error", Lang::get('login_failed')];
            $context = "";
        }

        return json_encode([
            "authorized" => $authorized,
            "title" => Config::get("page_settings", "title"),
            "module" => "Authentication",
            "toast" => $toast,
            "context" => $context
        ]);
    }


    public function signout()
    {
        $this->auth->destroy();
        $routeMod = Module::create($this->routeMod());
        $context = !$routeMod->isProtected() ? $this->routeMod() : "Home";
        return json_encode([
            "authorized" => Auth::check(),
            "title" => Config::get("page_settings", "title"),
            "module" => "Authentication",
            "toast" => ["success", Lang::get('signed_out')],
            "context" => $context
        ]);
    }


    public function register(Request $request)
    {
        $authorized = false;
        if ($this->auth->checkFormToken("reg", $request->getParameter("reg_token")) &&
                $request->getParameter("username") && $request->getParameter("password")) {
            $authorized = $this->auth->register($request->getParameter("username"),
                $request->getParameter("password"));
        }

        if ($authorized) {
            $response = "success";
            $context = "Authentication";
        } else {
            $response = "error";
            $context = "";
        }

        return json_encode([
            "authorized" => $authorized,
            "title" => Config::get("page_settings", "title"),
            "module" => "Authentication",
            "toast" => [$response, Lang::get($response)],
            "context" => $context
        ]);
    }

}