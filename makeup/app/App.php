<?php

namespace makeUp;

use makeUp\lib\Config;
use makeUp\lib\Template;
use makeUp\lib\Module;
use makeUp\lib\Lang;

class App extends Module {

    protected function build(): string
    {
        $modName = Module::getRoute();
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

        $m["[[NAVIGATION]]"] = Module::create("Navigation")->build(); // Adds the menu to the navbar
        $m["[[AUTHENTICATION]]"] = Module::create("Authentication")->build("form"); // Adds the login form to the navbar
        $m["[[LANGUAGE]]"] = Module::create("Language")->build(); // Adds the language selector to the navbar
        $m["[[SUBTITLE]]"] = Config::get("page_settings", "subtitle");

        $packageJson = json_decode(file_get_contents("../package.json"), true);
        
        $m["[[VERSION_NO]]"] = $packageJson['version'];
        $m["[[COPYRIGHT_YEAR]]"] = date("Y");

        return $this->getTemplate("App.html")->parse($m);
    }
}