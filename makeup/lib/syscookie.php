<?php

namespace makeup\lib;


/**
 * Class Session
 * @package makeup\lib
 */
class SysCookie
{
	private static $value = [];


	/**
	 * Decode the json value.
	 */
	public static function read($name = "__sys_makeup__")
	{
		if (isset($_COOKIE) && isset($_COOKIE[$name]))
			self::$value = json_decode($_COOKIE[$name], true);
	}


	/**
	 * Get a cookie value
	 * @param type $key
	 */
	public static function get($key)
	{
		return isset(self::$value[$key]) ? self::$value[$key] : null;
	}


	/**
	 * Set a cookie value
	 * @param type $key
	 * @param type $val
	 */
	public static function set($key, $val)
	{
		self::$value[$key] = $val;
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

