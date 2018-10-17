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
        /**** IMPORTANT: Module with page content must come first! *************/
        if (!$task) {
            $marker["##MODULES##"] = Module::create($module)->build();
        } else {
            $marker["##MODULES##"] = Module::create($module)->$task();
        }

        /**** Parsing the HTML head section ************************************/

        $marker["##TITLE##"] = Template::createTitleTag();
        $marker["##HTML_LANG##"] = Config::get("page_settings", "html_lang");

        $marker["##CONF_METATAGS##"] = Template::createMetaTags();
        $marker["##CONF_CSS_FILES##"] = Template::createStylesheetTags();
        $marker["##CONF_JS_FILES_HEAD##"] = Template::createJsScriptTagsHead();
        
        $marker["##COOKIE_NAME##"] = Config::get("cookie", "name") ?: "makeup";
        $marker["##COOKIE_EXP##"] = Config::get("cookie", "expires_days") ?: 0;
        $marker["##COOKIE_PATH##"] = Config::get("cookie", "path") ?: "/";

        $marker["##CONF_JS_FILES_BODY##"] = Template::createJsScriptTagsBody();
        
        $marker["##LANG_TITLE##"] = Lang::get("title");
        $marker["##LANG_SUBTITLE##"] = Lang::get("subtitle");
        $marker["##LANG_WIKI_BUTTON##"] = Lang::get("wiki_button");

        /**** Parsing the HTML body section ************************************/

        $marker["##NAVIGATION##"] = Module::create("navigation")->build(); // Connecting the menu to the navbar
        $marker["##LOGIN##"] = Module::create("login")->build("nav"); // Connecting the login form to the navbar
        $marker["##LANGUAGE_SELECTOR##"] = Module::create("language_selector")->build(); // Connecting the language selector
        $marker["##SUBTITLE##"] = Config::get("page_settings", "subtitle");

        return $this->getTemplate()->parse($marker);
    }
    
}      
