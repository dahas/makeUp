<?php

namespace makeup\app\controller;

/*******************************************************************************
 *
 * The app
 * 
 * This class is the main module. It creates the HTML skeleton, in which 
 * the modules are wrapped as subsets.
 *
 *******************************************************************************/

function autoloader($class)
{
    $path = dirname(dirname(dirname(dirname(__FILE__)))) . "/" . strtolower(str_replace("\\", "/", $class)) . ".php";
    require $path;
}

spl_autoload_register(__NAMESPACE__ . "\autoloader");

require dirname(dirname(dirname(dirname(__FILE__)))) . "/makeup/vendor/autoload.php";


use makeup\lib\Session;
use makeup\lib\Config;
use makeup\lib\Tools;
use makeup\lib\Template;
use makeup\lib\Module;
use makeup\lib\Lang;


class App extends Module
{
    // Calling the parent constructor is required!
    public function __construct()
    {
        parent::__construct();
    }


    /**
     * Build the complete HTML.
     * @param $module The module to be wrapped into the app. (If empty the default one as set in app.ini will be used)
     * @return string HTML
     */
    protected function build($module = "", $task = "") : string
    {
        $m = [];
        
        /**** IMPORTANT: Module with page content must come first! *************/
        if (!$task) {
            $m["##MODULES##"] = Module::create($module)->build();
        } else {
            $m["##MODULES##"] = Module::create($module)->$task();
        }

        /**** Parsing the HTML head section ************************************/

        $m["##TITLE##"] = Template::createTitleTag();
        $m["##HTML_LANG##"] = Config::get("page_settings", "html_lang");

        $m["##CONF_METATAGS##"] = Template::createMetaTags();
        $m["##CONF_CSS_FILES##"] = Template::createStylesheetTags();
        $m["##CONF_JS_FILES_HEAD##"] = Template::createJsScriptTagsHead();
        
        $m["##COOKIE_NAME##"] = Config::get("cookie", "name") ?: "makeup";
        $m["##COOKIE_EXP##"] = Config::get("cookie", "expires_days") ?: 0;
        $m["##COOKIE_PATH##"] = Config::get("cookie", "path") ?: "/";

        $m["##CONF_JS_FILES_BODY##"] = Template::createJsScriptTagsBody();
        
        $m["##LANG_TITLE##"] = Lang::get("title");
        $m["##LANG_SUBTITLE##"] = Lang::get("subtitle");
        $m["##LANG_WIKI_BUTTON##"] = Lang::get("wiki_button");

        /**** Parsing the HTML body section ************************************/

        $m["##NAVIGATION##"] = Module::create("navigation")->build(); // Connecting the menu to the navbar
        $m["##LOGIN##"] = Module::create("login")->build("nav"); // Connecting the login form to the navbar
        $m["##LANGUAGE_SELECTOR##"] = Module::create("language_selector")->build(); // Connecting the language selector
        $m["##SUBTITLE##"] = Config::get("page_settings", "subtitle");

        return $this->getTemplate()->parse($m);
    }
    
}      
