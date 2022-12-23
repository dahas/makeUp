<?php

/*******************************************************************************
 *                         _        _   _                                      *
 *         _ __ ___   __ _| | _____| | | |_ __                                 *
 *        | '_ ` _ \ / _` | |/ / _ \ | | | '_ \                                *
 *        | | | | | | (_| |   <  __/ |_| | |_) |                               *
 *        |_| |_| |_|\__,_|_|\_\___|\___/| .__/                                *
 *                                       |_|                                   *
 *                                                                             *
 *   makeUp is a PHP framework to build a Bootstrap single page application.   *
 *                                                                             * 
 *******************************************************************************/

namespace makeUp;

use makeUp\lib\Config;
use makeUp\lib\Session;
use makeUp\lib\Template;
use makeUp\lib\Module;
use makeUp\lib\Lang;

class App extends Module
{
    public function __construct()
    {
        parent::__construct();
    }


    protected function build() : string
    {
        $modName = Module::getModName();
        $m = [];
        
        /**** IMPORTANT: Module with page content must come first! *************/
        $m["[[CONTENT]]"] = Module::create($modName)->build();

        /**** Parsing the HTML head section ************************************/

        $m["[[TITLE]]"] = Template::createTitleTag();
        $m["[[HTML_LANG]]"] = Config::get("page_settings", "html_lang");

        $m["[[CONF_METATAGS]]"] = Template::createMetaTags();
        $m["[[CONF_CSS_FILES]]"] = Template::createStylesheetTags();
        $m["[[CONF_JS_FILES_HEAD]]"] = Template::createJsScriptTagsHead();
        
        $m["[[COOKIE_NAME]]"] = Config::get("cookie", "name") ?: "makeup";
        $m["[[COOKIE_EXP]]"] = Config::get("cookie", "expires_days") ?: 0;
        $m["[[COOKIE_PATH]]"] = Config::get("cookie", "path") ?: "/";

        $m["[[CONF_JS_FILES_BODY]]"] = Template::createJsScriptTagsBody();
        
        $m["[[LANG_TITLE]]"] = Lang::get("title");
        $m["[[LANG_SUBTITLE]]"] = Lang::get("subtitle");
        $m["[[LANG_WIKI_BUTTON]]"] = Lang::get("wiki_button");

        /**** Parsing the HTML body section ************************************/

        $m["[[NAVIGATION]]"] = Module::create("navigation")->build(); // Adds the menu to the navbar
        $m["[[AUTHENTICATION]]"] = Module::create("authentication")->build("form"); // Adds the login form to the navbar
        $m["[[LANGUAGE_SELECTOR]]"] = Module::create("language_selector")->build(); // Adds the language selector to the navbar
        $m["[[SUBTITLE]]"] = Config::get("page_settings", "subtitle");
        $m["[[RW]]"] = Config::get("app_settings", "url_rewriting");

        return $this->getTemplate()->parse($m);
    }
}      
