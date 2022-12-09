<?php

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
            $html = $this->getTemplate($template)->getSlice("{{SIGNOUT}}")->parse();
        } else {
            $html = $this->getTemplate($template)->getSlice("{{SIGNIN}}")->parse();
        }

        return $html;
    }


    private function buildFail() : string
    {
        $html = "";
        $template = "authentication.fail.html";
        $token = Tools::createFormToken();

        return $this->getTemplate($template)->parse();
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
        if (Tools::checkFormToken(RQ::post("token")) && RQ::POST('username') === 'user' && RQ::POST('password') === 'pass') {
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
