<?php declare(strict_types=1);

namespace makeUp\lib;


class Utils {
    private static $tokenArr = [];

    public static function loadIniFile(string $modName = "App", string $fileName = ""): array |false
    {
        if (!$fileName)
            $fileName = $modName . ".ini";

        $realPath = realpath('');

        if ($modName == "App")
            $path = str_replace("/public", "", str_replace("\\", "/", $realPath)) . "/makeup/app/App.ini";
        else
            $path = str_replace("/public", "", str_replace("\\", "/", $realPath)) . "/makeup/app/modules/$modName/$fileName";

        if (file_exists($path))
            return parse_ini_file($path, true);
        else
            return false;
    }

    public static function loadJsonLangFile(string $default = "_default"): array
    {
        $strings = [];
        $lang = self::getUserLanguageCode();
        $fpath = str_replace("/public", "", str_replace("\\", "/", realpath(''))) . "/makeup/lang/%s.json";

        $path = sprintf($fpath, strtolower($lang));
        $defPath = sprintf($fpath, $default);

        if (file_exists($path) && file_exists($defPath)) { // Translation available
            $strings['default'] = json_decode(file_get_contents($defPath), true);
            $strings['translation'] = json_decode(file_get_contents($path), true);
        } elseif (file_exists($defPath)) {
            $strings['default'] = json_decode(file_get_contents($defPath), true);
        }
        return $strings;
    }

    public static function getTranslation(): array
    {
        if (!Config::get("app_settings", "dev_mode") && Session::get("translation")) {
            $translation = Session::get("translation");
        } else {
            $translation = self::loadJsonLangFile();
            Session::set("translation", $translation);
        }

        return $translation;
    }

    public static function getUserLanguageCode(): string
    {
        Cookie::read(Config::get("cookie", "name"));
        if (!$langCode = Cookie::get("lang_code"))
            $langCode = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2) : Config::get("app_settings", "default_lang");

        return $langCode;
    }

    public static function getSupportedLanguages(): array
    {
        if (!Config::get("app_settings", "dev_mode") && Session::get("supported_languages")) {
            $languages = Session::get("supported_languages");
        } else {
            $path = str_replace("/public", "", str_replace("\\", "/", realpath(''))) . "/makeup/lang";
            $isoLangs = json_decode(file_get_contents($path . "/_iso.json"), true);
            $langFiles = scandir($path);

            $languages = [];
            foreach ($langFiles as $file) {
                if ($file != "." && $file != ".." && $file != "_iso.json") {
                    $lang = str_replace(".json", "", $file);
                    if ($lang == '_default') {
                        $lang = Config::get("app_settings", "default_lang");
                    }
                    $languages[$lang] = $isoLangs[$lang]["nativeName"] ?? null;
                }
            }
            Session::set("supported_languages", $languages);
        }

        return $languages;
    }

    public static function linkBuilder(string $mod, mixed $task = null, array $query = []): string
    {
        $link = "/$mod";
        $link .= $task ? "/$task" : "";
        $link .= !empty($query) ? "?" . http_build_query($query) : "";
        return $link;
    }

    public static function createFormToken(string $name): string
    {
        $expSecs = 5; // Token expires after this amount of seconds
        $timestamp = time();
        if ($timestamp >= Session::get($name . "_token_expires")) {
            $token = sha1($timestamp . random_int(1000, 9999));
            Session::set($name . "_token", $token);
            Session::set($name . "_token_expires", $timestamp + $expSecs);
            return $token;
        } else {
            return Session::get($name . "_token");
        }
    }

    public static function checkFormToken(string $name, string $token): bool
    {
        $valid = $token == Session::get($name . "_token");
        if (time() >= Session::get($name . "_token_expires")) {
            Session::clear($name . "_token");
        }
        return $valid;
    }

    public static function errorMessage(string $msg): string
    {
        return '<span style="font-size: 12px; font-weight: bold; color: red;">' . $msg . '</span>';
    }

    public static function arrayMerge(array $array1, array $array2): mixed
    {
        foreach ($array2 as $key => $val) {
            if (!is_array($val) && $val) {
                if (is_numeric($key))
                    $array1[] = $val;
                else
                    $array1[$key] = $val;
            } elseif (isset($array1[$key]) && is_array($val)) {
                $array1[$key] = self::arrayMerge($array1[$key], $val);
            } elseif (!isset($array1[$key])) {
                if (is_numeric($key))
                    $array1[] = $array2[$key];
                else
                    $array1[$key] = $array2[$key];
            }
        }
        return $array1;
    }

}