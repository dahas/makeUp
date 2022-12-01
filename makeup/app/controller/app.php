<?php

namespace makeUp;

/*******************************************************************************
 *                    _        _   _                                           *
 *    _ __ ___   __ _| | _____| | | |_ __                                      *
 *   | '_ ` _ \ / _` | |/ / _ \ | | | '_ \                                     *
 *   | | | | | | (_| |   <  __/ |_| | |_) |                                    *
 *   |_| |_| |_|\__,_|_|\_\___|\___/| .__/                                     *
 *                                  |_|                                        *
 *                                                                             *
 *   makeUp is a PHP framework to build a single page bootstrap application.   *
 *                                                                             * 
 *******************************************************************************/

use makeUp\lib\Config;
use makeUp\lib\Template;
use makeUp\lib\Module;
use makeUp\lib\Lang;

class App extends Module
{
    // Calling the parent constructor is required!
    public function __construct()
    {
        parent::__construct();
    }


    /**
     * @param string $module
     * @param string $task
     * @return string HTML
     */
    protected function build(string $module = "", string $task = "") : string
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
        $m["##AUTHENTICATION##"] = Module::create("authentication")->build("nav"); // Connecting the login form to the navbar
        $m["##LANGUAGE_SELECTOR##"] = Module::create("language_selector")->build(); // Connecting the language selector
        $m["##SUBTITLE##"] = Config::get("page_settings", "subtitle");

        return $this->getTemplate()->parse($m);
    }
    
}      
