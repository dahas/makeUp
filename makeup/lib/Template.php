<?php declare(strict_types = 1);

namespace makeUp\lib;
use makeUp\src\Config;
use makeUp\src\Utils;


final class Template
{
	private $html = "";

	public function __construct($file = '')
	{
		if ($file)
			$this->loadFile($file);
	}

	public static function load(string $modName, string $templateFile): Template
	{
		$realPath = realpath('');

		if ($modName == "App")
			$file = str_replace("/public", "", str_replace("\\", "/", $realPath)) . "/makeup/app/App.html";
		else
			$file = str_replace("/public", "", str_replace("\\", "/", $realPath)) . "/makeup/app/modules/$modName/$templateFile";

		return new Template($file);
	}

	public static function html(string $html): Template
	{
		$tmpl = new Template();
		$tmpl->html = $html;
		return $tmpl;
	}

	private function loadFile(string $file = ''): void
	{
		if (is_file($file))
			$this->html = file_get_contents($file);
		else
			$this->html = Utils::errorMessage("No valid template file: $file");
	}

	public function getSlice(string $marker): mixed
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

	private function replaceMarker(string $html, array $markerArr): string
	{
		foreach ($markerArr as $key => $val) {
			$html = str_replace($key, strval($val), $html);
		}
		return $html;
	}

	private function replaceSlice(string $html, array $slicesArr): string
	{
		foreach ($slicesArr as $key => $val) {
			$html = self::trimSlice($html, $key, $val);
		}
		return $html;
	}

	private static function trimSlice(string $html, string $marker, string $slice, int $recursive = 1): string
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

	public function parse(array $markerArr = array(), array $slicesArr = array()) : string
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

	public static function replaceBodyTag(string $bt, string $tmpl) : string
	{
		return $tmpl = str_replace("<body>", $bt, $tmpl);
	}

	public static function createMetaTags() : string
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

	public static function createTitleTag() : string
	{
		if (Config::get('page_settings', 'title')) {
			$title = Config::get('page_settings', 'title');

			return '<title>' . $title . '</title>';
		}
		return "";
	}

	public static function createStylesheetTags() : string
	{
		$tags = [];
		$str = '<link rel="stylesheet" href="%s" media="%s">';
		if (Config::get('additional_css_files')) {
			if (isset(Config::get('additional_css_files')["screen"])) {
				foreach (Config::get('additional_css_files')["screen"] as $href) {
					$tags[] = sprintf($str, $href, "screen");
				}
			}
			if (isset(Config::get('additional_css_files')["print"])) {
				foreach (Config::get('additional_css_files')["print"] as $href) {
					$tags[] = sprintf($str, $href, "print");
				}
			}
		}
		return implode("\n", $tags);
	}

	public static function createJsScriptTagsHead() : string
	{
		$tags = [];
		$str = '<script type="text/javascript" src="%s"></script>';
		if (Config::get('additional_js_files_head')) {
			if (isset(Config::get('additional_js_files_head')["js"])) {
				foreach (Config::get('additional_js_files_head')['js'] as $href) {
					$tags[] = sprintf($str, $href);
				}
			}
		}
		return implode("\n", $tags);
	}

	public static function createJsScriptTagsBody() : string
	{
		$tags = [];
		$str = '<script type="text/javascript" src="%s"></script>';
		if (Config::get('additional_js_files_body')) {
			if (isset(Config::get('additional_js_files_body')["js"])) {
				foreach (Config::get('additional_js_files_body')['js'] as $href) {
					$tags[] = sprintf($str, $href);
				}
			}
		}
		return implode("\n", $tags);
	}
}
