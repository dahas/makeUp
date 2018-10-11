<?php

namespace makeup\lib;


/**
 * Class Cookie
 * @package makeup\lib
 */
class Cookie
{
	private static $value = [];


	/**
	 * Decode the json value.
	 */
	public static function read($name)
	{
		if (isset($_COOKIE) && isset($_COOKIE[$name])) {
			self::$value = json_decode($_COOKIE[$name], true);
		}
	}


	/**
	 * Decode the json value.
	 */
	public static function create()
	{
		$name = Config::get("cookie", "name");
		$expDays = Config::get("cookie", "expires_days") ?: 0;
		$expires = $expDays == 0 ? 0 : time()+60*60*24*$expDays;
		$path = Config::get("cookie", "path") ?: "/";
		setrawcookie($name, json_encode(self::$value), $expires, $path, null);
	}


	/**
	 * Get a cookie value
	 * @param type $key
	 */
	public static function get($key)
	{
		return self::$value[$key] ?? null;
	}


	/**
	 * Set a cookie value
	 * @param type $key
	 * @param type $val
	 */
	public static function set($key, $val)
	{
		self::$value[$key] = $val;
		self::create();
	}


	/**
	 * Delete a cookie value
	 * @param type $key
	 */
	public static function clear($key)
	{
		self::$value[$key] = null;
		unset(self::$value[$key]);
	}


	/**
	 * Destroy the cookie
	 */
	public static function destroy()
	{
		self::$value = [];
		unset($_COOKIE);
	}


}

