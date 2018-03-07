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


class App extends Module
{
    // Calling the parent constructor is required!
    public function __construct()
    {
        parent::__construct();

        // Simulate login:
        Session::set("logged_in", false);
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
            $marker["##MODULES##"] = Module::create($module)->render();
        } else {
            $marker["##MODULES##"] = Module::create($module)->$task();
        }

        /**** Parsing the HTML head section ************************************/

        $marker["##TITLE##"] = Template::createTitleTag();
        $marker["##HTML_LANG##"] = Config::get("page_settings", "html_lang");
        $marker["##CONF_METATAGS##"] = Template::createMetaTags();
        $marker["##CONF_CSS_FILES##"] = Template::createStylesheetTags();
        $marker["##CONF_JS_FILES_HEAD##"] = Template::createJsScriptTagsHead();
        $marker["##CONF_JS_FILES_BODY##"] = Template::createJsScriptTagsBody();

        /**** Parsing the HTML body section ************************************/

        $marker["##NAVIGATION##"] = Module::create("navigation")->render(); // Connecting the menu to the navbar
        $marker["##SUBTITLE##"] = Config::get("page_settings", "subtitle");

        return $this->getTemplate()->parse($marker);
    }
    
}      
