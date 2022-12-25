<?php declare(strict_types=1);

namespace makeUp\lib;


class Utils {
    private static $bodyOnload = '';
    private static $debugArr = [];
    private static $tokenArr = [];

    public static function loadIniFile(string $modName = "App", string $fileName = ""): array |false
    {
        if (!$fileName)
            $fileName = $modName . ".ini";

        $realPath = realpath('');

        if ($modName == "App")
            $path = str_replace("/public", "", str_replace("\\", "/", $realPath)) . "/makeUp/app/App.ini";
        else
            $path = str_replace("/public", "", str_replace("\\", "/", $realPath)) . "/makeUp/app/modules/$modName/$fileName";

        if (file_exists($path))
            return parse_ini_file($path, true);
        else
            return false;
    }

    public static function loadJsonLangFile(string $default = "_default"): array
    {
        $strings = [];
        $lang = self::getUserLanguageCode();
        $fpath = str_replace("/public", "", str_replace("\\", "/", realpath(''))) . "/makeUp/lang/%s.json";

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
            $path = str_replace("/public", "", str_replace("\\", "/", realpath(''))) . "/makeUp/lang";
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

    public static function linkBuilder(string $mod, array $query = []): string
    {
        $link = "/$mod";
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

    public static function setBodyOnload($value): void
    {
        self::$bodyOnload .= $value;
    }

    public static function getBodyOnload(): string
    {
        return self::$bodyOnload;
    }

    public static function errorMessage(string $msg): string
    {
        return '<span style="font-size: 12px; font-weight: bold; color: red;">' . $msg . '</span>';
    }

    public static function upperCamelCase(string $input, string $separator = '_'): string
    {
        return str_replace($separator, '', ucwords($input, $separator));
    }

    public static function lowerCamelCase(string $input, string $separator = '_'): string
    {
        return str_replace($separator, '', lcfirst(ucwords($input, $separator)));
    }

    public static function camelCaseToUnderscore(string $input): string
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]+/', '_$0', $input));
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

    public static function debug(string $val = ""): void
    {
        if (Config::get("app_settings", "dev_mode")) {
            $bt = debug_backtrace();
            $caller = array_shift($bt);
            unset($caller["function"]);
            unset($caller["class"]);
            unset($caller["type"]);
            self::$debugArr[] = $caller;
            Session::set('_debug', self::$debugArr);
        } else {
            Session::clear('_debug');
        }
    }

    public static function renderDebugPanel(): string
    {
        $html = "";
        Cookie::read("__sys_makeup__");
        if (Cookie::get("panel_open") == true) {
            $dbgHandleIcon = "/div/img/close.png";
            $dbgHandleDspl = "block";
        } else {
            $dbgHandleIcon = "/div/img/open.png";
            $dbgHandleDspl = "none";
        }
        if (Config::get("app_settings", "dev_mode")) {
            $height = Session::get('_debug') ? 700 : 377;
            $html = '<script type="text/javascript" src="/div/system.js"></script>
<div style="position:fixed; bottom:0; right:0; z-index:99999; background: silver; border: 1px solid grey;">
  <div id="dbg-handle" style="float:left; width: 20px; height: 20px; padding: 0px 4px 4px 3px; cursor: pointer;" title="Debug panel"><img id="dbg-img" style="margin-top:-6px;" src="' . $dbgHandleIcon . '" height="14" /></div>
  <div id="dbg-frame" style="display:' . $dbgHandleDspl . '; float:right; width:500px;">
    <iframe src="/div/debug.php" style="width: 100%; height: ' . $height . 'px; border:none;"></iframe>
  </div>
</div>';
        }

        return $html;
    }

}