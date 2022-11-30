<?php

namespace makeup\lib;

/**
 * Class Session
 * @package makeup\lib
 */
class Session
{
	/**
	 * Start the session if it isn´t already.
	 */
	public static function start()
	{
		if (!isset($_SESSION))
			session_start();
	}


	public static function get(mixed $key) : mixed
	{
		return $_SESSION[$key] ?? null;
	}


	public static function set(string $key, mixed $val) : void
	{
		$_SESSION[$key] = $val;
	}


	/**
	 * Delete a session value
	 * @param type $key
	 */
	public static function clear($key)
	{
		$_SESSION[$key] = null;
		unset($_SESSION[$key]);
	}


	/**
	 * Destroy the session
	 */
	public static function destroy()
	{
		$_SESSION = null;
		unset($_SESSION);
	}


}

