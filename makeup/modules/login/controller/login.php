<?php

use makeup\lib\Module;
use makeup\lib\RQ;
use makeup\lib\Config;
use makeup\lib\Tools;
use makeup\lib\Session;


/**
 * This is a system module
 */
class Login extends Module
{
    public function __construct()
    {
        parent::__construct();
    }


    protected function build($formVariant = "") : string
    {
        if ($formVariant) {
            return $this->buildForm($formVariant);
        } else {
            return $this->getTemplate()->parse([
                "##FORM##" => $this->buildForm("page")
            ]);
        }
    }


    private function buildForm($formVariant) : string
    {
        $html = "";
        $template = $formVariant == "page" ? "login.page.html" : "login.nav.html";
        $token = Tools::createFormToken();

        if (Session::get("logged_in")) {
            $formAction = "signout";
            $loginStateSlice = "{{SIGNOUT}}";
            $redirect = Config::get("signout", "redirect");
        } else {
            $loginStateSlice = "{{SIGNIN}}";
            $formAction = "signin";
            $redirect = RQ::get("mod");
        }

        $html = $this->getTemplate($template)->getSlice($loginStateSlice)->parse([
            "##FORM_ACTION##" => Tools::linkBuilder($this->modName, $formAction),
            "##TOKEN##" => $token,
            "##REDIRECT##" => $redirect
        ]);

        return $html;
    }


    /**
     * Authenticate user whenn signing in
     */
    public function signin()
    {
        // Simulate login:
        if (Tools::checkFormToken(RQ::post("token"))) {
            Session::set("logged_in", true); // Simulate login
        }
        header("Location: " . Tools::linkBuilder(RQ::post("redirect")));
    }


    /**
     * Logout user
     */
    public function signout()
    {
        Session::set("logged_in", false); // Simulate logout
        header("Location: " . Tools::linkBuilder(RQ::post("redirect")));
    }

}
