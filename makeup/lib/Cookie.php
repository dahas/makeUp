<?php declare(strict_types = 1);

namespace makeUp\lib;


class Cookie
{
	private static $value = [];

	public static function read(string $name) : void
	{
		if (isset($_COOKIE) && isset($_COOKIE[$name])) {
			self::$value = json_decode(base64_decode($_COOKIE[$name]), true);
		}
	}

	public static function create() : void
	{
		$name = Config::get("cookie", "name");
		$expDays = Config::get("cookie", "expires_days") ?: 0;
		$expires = $expDays == 0 ? 0 : time()+60*60*24*$expDays;
		$path = Config::get("cookie", "path") ?: "/";
		setrawcookie($name, base64_encode(json_encode(self::$value)), $expires, $path);
	}

	public static function get(string $key) : mixed
	{
		return self::$value[$key] ?? null;
	}

	public static function set(string $key, mixed $val) : void
	{
		self::$value[$key] = $val;
		self::create();
	}

	public static function clear(string $key) : void
	{
		self::$value[$key] = null;
		unset(self::$value[$key]);
	}

	public static function destroy() : void
	{
		self::$value = [];
		unset($_COOKIE);
	}


}

