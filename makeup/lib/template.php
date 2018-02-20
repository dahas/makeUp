<?php

namespace makeup\lib;


/**
 * Class Template
 * @package makeup\lib
 */
class Template
{
	private $html = "";


	public function __construct($file = '')
	{
		if ($file)
			$this->loadFile($file);
	}


	/**
	 * @param $modName
	 * @param $templateFile
	 * @return Template
	 */
	public static function load($modName, $templateFile)
	{
		$modNsArr = explode("\\", $modName);
		$className = array_pop($modNsArr);

		$modName = Tools::camelCaseToUnderscore($className);

		$realPath = realpath(null);

		if ($modName == "app")
			$file = str_replace("/public", "", str_replace("\\", "/", realpath(null))) . "/makeup/$modName/view/$templateFile";
		else
			$file = str_replace("/public", "", str_replace("\\", "/", realpath(null))) . "/makeup/modules/$modName/view/$templateFile";

		return new Template($file);
	}


	/**
	 * @param $html
	 * @return Template
	 */
	public static function html($html)
	{
		$tmpl = new Template();
		$tmpl->html = $html;
		return $tmpl;
	}


	private function loadFile($file = '')
	{
		if (is_file($file))
			$this->html = file_get_contents($file);
		else
			$this->html = Tools::errorMessage("No valid template file: $file");
	}


	public function getSlice($marker)
	{
		$start = strpos($this->html, $marker);
		if ($start === false) {
			return '';
		}
		$start += strlen($marker);
		$stop = strpos($this->html, $marker, $start);
		if ($stop === false) {
			return '';
		}
		$html = substr($this->html, $start, $stop - $start);

		$matches = array();
		if (preg_match('/^([^\<]*\-\-\>)(.*)(\<\!\-\-[^\>]*)$/s', $html, $matches) === 1 || preg_match('/^([^\<]*\-\-\>)(.*)$/s', $html, $matches) === 1) {
			return self::html($matches[2]);
		}
		$matches = array();
		if (preg_match('/(.*)(\<\!\-\-[^\>]*)$/s', $html, $matches) === 1) {
			return self::html($matches[1]);
		}
		return self::html($html);
	}


	private function replaceMarker($html, $markerArr)
	{
		foreach ($markerArr as $key => $val) {
			$html = str_replace($key, $val, $html);
		}
		return $html;
	}


	private function replaceSlice($html, $slicesArr)
	{
		foreach ($slicesArr as $key => $val) {
			$html = self::trimSlice($html, $key, $val);
		}
		return $html;
	}


	private static function trimSlice($html, $marker, $slice, $recursive = 1)
	{
		$start = strpos($html, $marker);
		if ($start === false) {
			return $html;
		}
		$startAM = $start + strlen($marker);
		$stop = strpos($html, $marker, $startAM);
		if ($stop === false) {
			return $html;
		}
		$stopAM = $stop + strlen($marker);
		$before = substr($html, 0, $start);
		$after = substr($html, $stopAM);
		$between = substr($html, $startAM, $stop - $startAM);
		if ($recursive) {
			$after = self::trimSlice($after, $marker, $slice, $recursive);
		}
		$matches = array();
		if (preg_match('/^(.*)\<\!\-\-[^\>]*$/s', $before, $matches) === 1) {
			$before = $matches[1];
		}
		if (is_array($slice)) {
			$matches = array();
			if (preg_match('/^([^\<]*\-\-\>)(.*)(\<\!\-\-[^\>]*)$/s', $between, $matches) === 1) {
				$between = $matches[2];
			} elseif (preg_match('/^(.*)(\<\!\-\-[^\>]*)$/s', $between, $matches) === 1) {
				$between = $matches[1];
			} elseif (preg_match('/^([^\<]*\-\-\>)(.*)$/s', $between, $matches) === 1) {
				$between = $matches[2];
			}
		}
		$matches = array();
		if (preg_match('/^[^\<]*\-\-\>(.*)$/s', $after, $matches) === 1) {
			$after = $matches[1];
		}
		if (is_array($slice)) {
			$between = $slice[0] . $between . $slice[1];
		} else {
			$between = $slice;
		}
		return $before . $between . $after;
	}


	public function parse($markerArr = array(), $slicesArr = array())
	{
		$html = $this->html;
		if (!empty($markerArr)) {
			$html = $this->replaceMarker($html, $markerArr);
		}
		if (!empty($slicesArr)) {
			$html = $this->replaceSlice($html, $slicesArr);
		}
		return $html;
	}


	public static function replaceBodyTag($bt, $tmpl)
	{
		return $tmpl = str_replace("<body>", $bt, $tmpl);
	}


	/**
	 * Creates meta tags
	 * 
	 * @return string
	 */
	public static function createMetaTags()
	{
		$tags = [];
		$strHttpEquiv = '<meta http-equiv="%s" content="%s">';
		$strMetaTag = '<meta name="%s" content="%s">';
		if (Config::get('meta_http_equiv')) {
			foreach (Config::get('meta_http_equiv') as $equiv => $content) {
				$tags[] = sprintf($strHttpEquiv, $equiv, $content);
			}
		}
		if (Config::get('metatags')) {
			$tags[] = "";
			foreach (Config::get('metatags') as $name => $content) {
				if ($name == strtolower("charset"))
					$tags[] = '<meta charset="' . $content . '">';
				else
					$tags[] = sprintf($strMetaTag, $name, $content);
			}
		}
		return implode("\n", $tags);
	}


	/**
	 * Creates the title tag
	 * 
	 * @return string
	 */
	public static function createTitleTag()
	{
		if (Config::get('page_settings', 'title')) {
			return '<title>' . Config::get('page_settings', 'title') . '</title>';
		}
		return "";
	}


	/**
	 * Creates stylesheet tags
	 * 
	 * @return string
	 */
	public static function createStylesheetTags()
	{
		$tags = [];
		$str = '<link rel="stylesheet" href="%s" media="%s">';
		if (Config::get('additional_css_files')) {
			foreach (Config::get('additional_css_files')["screen"] as $href) {
				$tags[] = sprintf($str, $href, "screen");
			}
			if (isset(Config::get('additional_css_files')["print"])) {
				foreach (Config::get('additional_css_files')["print"] as $href) {
					$tags[] = sprintf($str, $href, "print");
				}
			}
		}
		return implode("\n", $tags);
	}


	/**
	 * Creates JavaScript files tags in the head section
	 * 
	 * @return string
	 */
	public static function createJsFilesHeadTags()
	{
		$tags = [];
		$str = '<script type="text/javascript" src="%s"></script>';
		if (Config::get('additional_js_files_head')) {
			foreach (Config::get('additional_js_files_head')['js'] as $href) {
				$tags[] = sprintf($str, $href);
			}
		}
		return implode("\n", $tags);
	}


	/**
	 * Creates JavaScript files tags in the body section before the closing tag. 
	 * 
	 * @return string
	 */
	public static function createJsFilesBodyTags()
	{
		$tags = [];
		$str = '<script type="text/javascript" src="%s"></script>';
		if (Config::get('additional_js_files_body')) {
			foreach (Config::get('additional_js_files_body')['js'] as $href) {
				$tags[] = sprintf($str, $href);
			}
			if (Config::get("app_settings", "dev_mode")) {
				$tags[] = sprintf($str, "/div/system.js");
			}
		}
		return implode("\n", $tags);
	}


}

