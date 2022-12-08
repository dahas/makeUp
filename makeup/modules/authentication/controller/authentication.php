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


    protected function build($variant = "") : string
    {
        if ($variant) {
            return $this->buildForm($variant);
        } else {
            return $this->render([
                "##FORM##" => $this->buildForm("page")
            ]);
        }
    }

    function render(array $m = [], array $s = []): string
	{
		$html = $this->getTemplate()->parse($m, $s);

		if (!RQ::GET('app') || RQ::GET('app') == 'wrap')
			return $html;

		$json = json_encode([
			"title" => Config::get("page_settings", "title"),
			"module" => $this->modName,
			"segments" => [
				[
					"html" => $html,
					"target" => 'content'
				],
				[
					"html" => $this->buildForm('nav'),
					"target" => 'authentication'
				]
			]
		]);

		return $json;
	}


    private function buildForm($variant) : string
    {
        $html = "";
        $template = $variant == "page" ? "authentication.response.html" : "authentication.form.html";
        $token = Tools::createFormToken();

        if (Session::get("logged_in")) {
            $html = $this->getTemplate($template)->getSlice("{{SIGNOUT}}")->parse([
                "##FORM_ACTION##" => Tools::linkBuilder($this->modName, "signout"),
                "##TOKEN##" => $token
            ]);
        } else {
            $html = $this->getTemplate($template)->getSlice("{{SIGNIN}}")->parse([
                "##FORM_ACTION##" => Tools::linkBuilder($this->modName, "signin"),
                "##REGISTER_LINK##" => Tools::linkBuilder("registration"),
                "##TOKEN##" => $token
            ]);
        }

        return $html;
    }


    public function signin()
    {
        // Simulate login:
        if (Tools::checkFormToken(RQ::post("token"))) {
            if (RQ::POST('username') !== 'user' || RQ::POST('password') !== 'pass') {

            }
                
            Session::set("logged_in", true);

        }

        return $this->build();
    }


    public function signout()
    {
        Session::set("logged_in", false); // Simulate logout
        return $this->build();
    }

}
