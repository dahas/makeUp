<?php declare(strict_types = 1);

namespace makeUp\lib;


class Config
{
    private static $modName = array();
    private static $config = array();

    public static function init($modName = "App")
    {
        self::$modName = $modName;

        if (empty(self::$config)) {
            $appConfig = Utils::loadIniFile();
            $appConfig['additional_css_files']['screen'] = self::setCssFilesPath($appConfig, 'screen');
            $appConfig['additional_css_files']['print'] = self::setCssFilesPath($appConfig, 'print');
            $appConfig['additional_js_files_head']['js'] = self::setJsFilesPath($appConfig, 'head');
            $appConfig['additional_js_files_body']['js'] = self::setJsFilesPath($appConfig, 'body');
        } else {
            $appConfig = self::$config;
        }

        if ($modName != "App") {
            if ($modConfig = Utils::loadIniFile($modName)) {
                $modConfig['additional_css_files']['screen'] = self::setCssFilesPath($modConfig, 'screen');
                $modConfig['additional_css_files']['print'] = self::setCssFilesPath($modConfig, 'print');
                $modConfig['additional_js_files_head']['js'] = self::setJsFilesPath($modConfig, 'head');
                $modConfig['additional_js_files_body']['js'] = self::setJsFilesPath($modConfig, 'body');
                $appConfig = Utils::arrayMerge($appConfig, $modConfig);
            }
        } 
        
        self::$config = $appConfig;

        $_SESSION['_config'] = $appConfig;
    }

    public static function get() : mixed
    {
        $args = func_get_args();
        if ($args) {
            if (count($args) == 1) {
                $arg = self::$config[$args[0]] ?? null;
            }
            if (count($args) == 2) {
                $arg = self::$config[$args[0]][$args[1]] ?? null;
                $arg = self::translateArgument($arg);
            }
            if (count($args) == 3) {
                $arg = self::$config[$args[0]][$args[1]][$args[2]] ?? null;
                $arg = self::translateArgument($arg);
            }
            return $arg;
        }
        return self::$config;
    }

    private static function translateArgument($arg) : string
    {
        $arg = $arg ?: "";
        $pos = strpos($arg, "*");
        if ($pos !== false && $pos == 0) {
            $string = str_replace("*", "", $arg);
            if (!$arg = Lang::get(self::$modName, $string))
                $arg = Lang::get("App", $string);
        }
        return $arg;
    }

    public static function getAdditionalCssFiles() : array
    {
        return self::removeDuplicateFiles(self::$config['additional_css_files']);
    }

    public static function getAdditionalJsFilesHead() : array
    {
        return self::removeDuplicateFiles(self::$config['additional_js_files_head']);
    }

    public static function getAdditionalJsFilesBody() : array
    {
        return self::removeDuplicateFiles(self::$config['additional_js_files_body']);
    }

    public static function setAdditionalCssFiles(array $files = array()) : void
    {
        if (isset($files['css'])) {
            self::$config['additional_css_files'] = array_merge_recursive(self::$config['additional_css_files'], $files);
        }
    }

    public static function setAdditionalJsFilesHead(array $files = array()) : void
    {
        if (isset($files['js'])) {
            self::$config['additional_js_files_head'] = array_merge_recursive(self::$config['additional_js_files_head'], $files);
        }
    }

    public static function setAdditionalJsFilesBody(array $files = array()) : void
    {
        if (isset($files['js'])) {
            self::$config['additional_js_files_body'] = array_merge_recursive(self::$config['additional_js_files_body'], $files);
        }
    }

    private static function setCssFilesPath(array|bool $config, string $type) : array
    {
        $newPath = [];
        if ($config && isset($config['additional_css_files'][strtolower($type)][0]) && $config['additional_css_files'][strtolower($type)][0]) {
            foreach ($config['additional_css_files'][strtolower($type)] as $file) {
                $newPath[] = $file;
            }
        }
        return $newPath;
    }

    private static function setJsFilesPath(array|bool $config, string $type) : array
    {
        $newPath = [];
        if (isset($config['additional_js_files_'.strtolower($type)]['js'][0]) && $config['additional_js_files_'.strtolower($type)]['js'][0]) {
            foreach ($config['additional_js_files_'.strtolower($type)]['js'] as $file) {
                $newPath[] = $file;
            }
        }
        return $newPath;
    }

    private static function removeDuplicateFiles(array $array) : array
    {
        $fixedArr = array();
        if (isset($array['css'])) {
            $fixedArr['css'] = array_unique($array['css']);
        }

        if (isset($array['js'])) {
            $fixedArr['js'] = array_unique($array['js']);
        }

        return $fixedArr;
    }

}
