<?php

namespace makeup\lib;

/**
 * Class Config
 * @package makeup\lib
 */
class Config
{
    private static $config = array();

    public static function init($moduleFileName = "app")
    {
        if (empty(self::$config)) {
            $appConfig = Tools::loadIniFile();
            $appConfig['additional_css_files']['screen'] = self::setCssScreenFilesPath($appConfig);
            $appConfig['additional_css_files']['print'] = self::setCssPrintFilesPath($appConfig);
            $appConfig['additional_js_files_head']['js'] = self::setJsFilesHeadPath($appConfig);
            $appConfig['additional_js_files_body']['js'] = self::setJsFilesBodyPath($appConfig);
        } else {
            $appConfig = self::$config;
        }

        if ($moduleFileName != "app") {
            if ($modConfig = Tools::loadIniFile($moduleFileName)) {
                $modConfig['additional_css_files']['screen'] = self::setCssScreenFilesPath($modConfig, $moduleFileName);
                $modConfig['additional_css_files']['print'] = self::setCssPrintFilesPath($modConfig, $moduleFileName);
                $modConfig['additional_js_files_head']['js'] = self::setJsFilesHeadPath($modConfig, $moduleFileName);
                $modConfig['additional_js_files_body']['js'] = self::setJsFilesBodyPath($modConfig, $moduleFileName);
                $appConfig = Tools::arrayMerge($appConfig, $modConfig);
            }
        } 
        
        self::$config = $appConfig;

        $_SESSION['_config'] = $appConfig;
    }

    /**
     * @param $entry
     * @return mixedy
     */
    public static function get()
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

    private static function translateArgument($arg)
    {
        $pos = strpos($arg, "*");
        if ($pos !== false && $pos == 0) {
            $string = str_replace("*", "", $arg);
            $modName = RQ::get("mod") && RQ::get("mod") != self::$config['app_settings']['default_module'] ? RQ::get("mod") : "app";
            if (!$arg = Lang::get($modName, $string))
                $arg = Lang::get("app", $string);
        }
        return $arg;
    }

    /**
     * @return array
     */
    public static function getAdditionalCssFiles()
    {
        return self::removeDuplicateFiles(self::$config['additional_css_files']);
    }

    /**
     * @return array
     */
    public static function getAdditionalJsFilesHead()
    {
        return self::removeDuplicateFiles(self::$config['additional_js_files_head']);
    }

    /**
     * @return array
     */
    public static function getAdditionalJsFilesBody()
    {
        return self::removeDuplicateFiles(self::$config['additional_js_files_body']);
    }

    /**
     * @param array $files
     */
    public static function setAdditionalCssFiles($files = array())
    {
        if (isset($files['css'])) {
            self::$config['additional_css_files'] = array_merge_recursive(self::$config['additional_css_files'], $files);
        }
    }

    /**
     * @param array $files
     */
    public static function setAdditionalJsFilesHead($files = array())
    {
        if (isset($files['js'])) {
            self::$config['additional_js_files_head'] = array_merge_recursive(self::$config['additional_js_files_head'], $files);
        }
    }

    /**
     * @param array $files
     */
    public static function setAdditionalJsFilesBody($files = array())
    {
        if (isset($files['js'])) {
            self::$config['additional_js_files_body'] = array_merge_recursive(self::$config['additional_js_files_body'], $files);
        }
    }

    /**
     * @param $config
     * @return array
     */
    private static function setCssScreenFilesPath($config)
    {
        if (isset($config['additional_css_files']['screen'][0]) && $config['additional_css_files']['screen'][0]) {
            $newPath = [];
            foreach ($config['additional_css_files']['screen'] as $file) {
                $newPath[] = $file;
            }
            return $newPath;
        }
    }

    /**
     * @param $config
     * @return array
     */
    private static function setCssPrintFilesPath($config)
    {
        if (isset($config['additional_css_files']['print'][0]) && $config['additional_css_files']['print'][0]) {
            $newPath = [];
            foreach ($config['additional_css_files']['print'] as $file) {
                $newPath[] = $file;
            }
            return $newPath;
        }
    }

    /**
     * @param $config
     * @return array
     */
    private static function setJsFilesHeadPath($config)
    {
        if (isset($config['additional_js_files_head']['js'][0]) && $config['additional_js_files_head']['js'][0]) {
            $newPath = [];
            foreach ($config['additional_js_files_head']['js'] as $file) {
                $newPath[] = $file;
            }
            return $newPath;
        }
    }

    /**
     * @param $config
     * @return array
     */
    private static function setJsFilesBodyPath($config)
    {
        if (isset($config['additional_js_files_body']['js'][0]) && $config['additional_js_files_body']['js'][0]) {
            $newPath = [];
            foreach ($config['additional_js_files_body']['js'] as $file) {
                $newPath[] = $file;
            }
            return $newPath;
        }
    }

    /**
     * @param $array
     * @return array
     */
    private static function removeDuplicateFiles($array)
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
