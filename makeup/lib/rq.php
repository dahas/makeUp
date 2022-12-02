<?php

namespace makeUp\lib;


class RQ
{
	public static function init() : void
	{
		if (!isset($_GET["mod"])) 
			$_GET["mod"] = Config::get("app_settings", "default_module");

		$_GET = self::parseQueryString();
		$_POST = self::parseFormData();
	}

	public static function GET(string $key) : mixed
	{
		return $_GET[$key] ?? null;
	}

	public static function POST(string $key) : mixed
	{
		return $_POST[$key] ?? null;
	}

	public static function parseQueryString() : array
	{
		return array_map('self::filterInput', $_GET);
	}

	public static function parseFormData() : array
	{
		return array_map('self::filterInput', $_POST);
	}

	private static function filterInput($input) : string
	{
		return filter_var(strip_tags(rawurldecode($input)), FILTER_SANITIZE_FULL_SPECIAL_CHARS);
	}

}

