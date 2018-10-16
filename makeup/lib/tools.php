<?php

namespace makeup\lib;


/**
 * This class contains several useful and necessary functions.
 * Some of them are only used by the framework itself.
 *
 * Class Tools
 * @package makeup\lib
 */
class Tools
{
	private static $bodyOnload = '';
	
	private static $debugArr = [];
	
	
	/**
	 * Loads an ini file. Either the one that belongs to the module, 
	 * or a special one.
	 * @param type $modName
	 * @param string $fileName
	 * @return type
	 */
	public static function loadIniFile($modName = "app", $fileName = "")
	{
		if (!$fileName)
			$fileName = $modName . ".ini";

		$realPath = realpath(null);
	
		if (strtolower($modName) == "app")
			$path = str_replace("/public", "", str_replace("\\", "/", realpath(null))) . "/makeup/app/config/app.ini";
		else
			$path = str_replace("/public", "", str_replace("\\", "/", realpath(null))) . "/makeup/modules/$modName/config/$fileName";
		
		if(file_exists($path))
			return parse_ini_file($path, true);
		else
			return null;
	}

	/**
     * Loads the language json file.
     * @param string $lang
     * @return array|null
     */
    public static function loadJsonLangFile()
    {
		$lang = self::getUserLanguageCode();
		$fpath = str_replace("/public", "", str_replace("\\", "/", realpath(null))) . "/makeup/lang/%s.json";
		
        $path = sprintf($fpath, strtolower($lang));
        if (file_exists($path)) {
            return json_decode(file_get_contents($path), true);
        } else {
			$path = sprintf($fpath, Config::get("app_settings", "default_lang"));
            if (file_exists($path)) {
				return json_decode(file_get_contents($path), true);
			} else {
				return null;
			}
        }
    }


	public static function getTranslation()
	{
		if (!Config::get("app_settings", "dev_mode") && Session::get("translation")) {
			$translation = Session::get("translation");
		} else {
			$translation = self::loadJsonLangFile();
			Session::set("translation", $translation);
		}
		
		return $translation;
	}


	public static function changeTranslation()
	{
		Session::clear("translation");
		$referer = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
		$redirect = str_replace(['?change_lang=1','&change_lang=1'],['',''],$referer);
		header("Location: $redirect");
	}


	public static function getUserLanguageCode()
	{
		Cookie::read(Config::get("cookie", "name"));
		if (!$langCode = Cookie::get("lang_code"))
			$langCode = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);

		return $langCode;
	}


	public static function getSupportedLanguages()
	{
		if (!Config::get("app_settings", "dev_mode") && Session::get("supported_languages")) {
			$languages = Session::get("supported_languages");
		} else {
			$path = str_replace("/public", "", str_replace("\\", "/", realpath(null))) . "/makeup/lang";
			$isoLangs = json_decode(file_get_contents($path . "/_iso.json"), true);
			$langFiles = scandir($path);

			$languages = [];
			foreach ($langFiles as $file) {
				if ($file != "." && $file != ".." && $file != "_iso.json") {
					$lang = str_replace(".json", "", $file);
					$languages[$lang] = $isoLangs[$lang]["nativeName"] ?? null;
				}
			}
			Session::set("supported_languages", $languages);
		}
		
		return $languages;
	}


	/**
	 * @param $value
	 */
	public static function setBodyOnload($value)
	{
		self::$bodyOnload .= $value;
	}


	/**
	 * @return string
	 */
	public static function getBodyOnload()
	{
		return self::$bodyOnload;
	}


	/**
	 * @param $msg
	 * @return string
	 */
	public static function errorMessage($msg)
	{
		return '<span style="font-size: 12px; font-weight: bold; color: red;">' . $msg . '</span>';
	}


	/**
	 * @param $input
	 * @param string $separator
	 * @return mixed
	 */
	public static function upperCamelCase($input, $separator = '_')
	{
		return str_replace($separator, '', ucwords($input, $separator));
	}


	/**
	 * @param $input
	 * @param string $separator
	 * @return mixed
	 */
	public static function lowerCamelCase($input, $separator = '_')
	{
		return str_replace($separator, '', lcfirst(ucwords($input, $separator)));
	}


	/**
	 * @param $input
	 * @return string
	 */
	public static function camelCaseToUnderscore($input)
	{
		return strtolower(preg_replace('/(?<!^)[A-Z]+/', '_$0', $input));
	}


	/**
	 * Merge 2 arrays
	 *
	 * @param $array1 appConfig
	 * @param $array2 modConfig
	 * @return mixed
	 */
	public static function arrayMerge($array1, $array2)
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


	/**
	 * Debug output in an iframe
	 * @param type $val
	 */
	public static function debug($val="")
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


	/**
	 * Debug output in an iframe
	 * @param type $val
	 */
	public static function renderDebugPanel()
	{
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
  <div id="dbg-handle" style="float:left; width: 20px; height: 20px; padding: 0px 4px 4px 3px; cursor: pointer;" title="Debug panel"><img id="dbg-img" style="margin-top:-6px;" src="'.$dbgHandleIcon.'" height="14" /></div>
  <div id="dbg-frame" style="display:'.$dbgHandleDspl.'; float:right; width:500px;">
    <iframe src="/div/debug.php" style="width: 100%; height: '.$height.'px; border:none;"></iframe>
  </div>
</div>';
			return $html;
		}
	}


}

