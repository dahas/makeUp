<?php

use makeUp\lib\Lang;
use makeUp\lib\Module;
use makeUp\lib\RQ;
use makeUp\lib\Config;
use makeUp\lib\Tools;
use makeUp\lib\Session;


class Authentication extends Module
{
    public function __construct()
    {
        parent::__construct();
    }


    protected function build(string $variant = "") : string
    {
        $formHTML = $this->buildForm();
        $responseHTML = $this->getTemplate()->parse(["[[FORM]]" => $this->buildResponse()]);
        $failHTML = $this->getTemplate()->parse(["[[FORM]]" => $this->buildFail()]);
        
        return match ($variant) {
            default => $formHTML,
            "response" => $this->render($responseHTML),
            "fail" => $this->render($failHTML)
        };
    }

    function render(string $html = ""): string
	{
		if (!RQ::GET('render') || RQ::GET('render') == 'html')
			return $html;

		$json = json_encode([
			"title" => Config::get("page_settings", "title"),
			"module" => $this->modName,
			"segments" => [
                ["target" => 'content', "html" => $html],
                ["target" => 'authentication', "html" => $this->buildForm()]
            ]
		]);

		return $json;
	}


    private function buildResponse() : string
    {
        $html = "";
        $template = "authentication.response.html";
        $token = Tools::createFormToken();

        if (Session::get("logged_in")) {
            $m["[[SIGNED_IN]]"] = Lang::get("signed_in");
            $html = $this->getTemplate($template)->getSlice("{{SIGNOUT}}")->parse($m);
        } else {
            $m["[[SIGNED_OUT]]"] = Lang::get("signed_out");
            $html = $this->getTemplate($template)->getSlice("{{SIGNIN}}")->parse($m);
        }

        return $html;
    }


    private function buildFail() : string
    {
        $html = "";
        $template = "authentication.fail.html";
        $token = Tools::createFormToken();

        $m["[[LOGIN_FAILED]]"] = Lang::get("login_failed");
        return $this->getTemplate($template)->parse($m);
    }


    private function buildForm() : string
    {
        $html = "";
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
                "[[REGISTER_LINK]]" => Tools::linkBuilder("registration"),
                "[[TOKEN]]" => $token
            ]);
        }

        return $html;
    }


    public function signin()
    {
        // Simulate login:
        $username = 'user';
        $password = 'pass';

        if (Tools::checkFormToken(RQ::post("token")) && RQ::POST('username') === $username && RQ::POST('password') === $password) {
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

}
