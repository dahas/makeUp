<?php

use makeUp\lib\Module;
use makeUp\lib\RQ;
use makeUp\lib\Config;
use makeUp\lib\Tools;
use makeUp\lib\Session;


/**
 * This is a system module
 */
class Authentication extends Module
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
        $template = $formVariant == "page" ? "authentication.page.html" : "authentication.nav.html";
        $token = Tools::createFormToken();

        if (Session::get("logged_in")) {
            $html = $this->getTemplate($template)->getSlice("{{SIGNOUT}}")->parse([
                "##FORM_ACTION##" => Tools::linkBuilder($this->modName, "signout"),
                "##TOKEN##" => $token,
                "##REDIRECT##" => Config::get("signout", "redirect") ?: RQ::get("mod")
            ]);
        } else {
            $html = $this->getTemplate($template)->getSlice("{{SIGNIN}}")->parse([
                "##FORM_ACTION##" => Tools::linkBuilder($this->modName, "signin"),
                "##REGISTER_LINK##" => Tools::linkBuilder("registration"),
                "##TOKEN##" => $token,
                "##REDIRECT##" => Config::get("signin", "redirect") ?: RQ::get("mod")
            ]);
        }

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
