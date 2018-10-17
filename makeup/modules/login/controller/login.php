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
        switch ($formVariant)
        {
            case "page":
                return $this->formPage();
            case "nav":
                return $this->formNav();
        }

        return $this->getTemplate()->parse([
            "##FORM##" => $this->formPage()
        ]);
    }


    private function formPage() : string
    {
        return $this->getTemplate("login.page.html")->parse();
    }


    private function formNav() : string
    {
        return $this->getTemplate("login.nav.html")->parse([
            "##FORM_ACTION##" => Tools::linkBuilder($this->modName, "signin"),
            "##REFERER##" => RQ::get("mod")
        ]);
    }


    /**
     * Authenticate user whenn signing in
     */
    public function signin()
    {
        // Simulate login:
        Session::set("logged_in", true);
        // Redirect
        header("Location: " . Tools::linkBuilder(RQ::post("referer")));
    }


    /**
     * Logout user
     */
    public function signout()
    {
        // Simulate logout:
        Session::set("logged_in", false);
        // Redirect
        header("Location: " . Tools::linkBuilder(RQ::post("referer")));
    }

}
