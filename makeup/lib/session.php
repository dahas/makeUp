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


	/**
	 * Get a session value
	 * @param type $key
	 */
	public static function get($key)
	{
		return isset($_SESSION[$key]) ? $_SESSION[$key] : null;
	}


	/**
	 * Set a session value
	 * @param type $key
	 * @param type $val
	 */
	public static function set($key, $val)
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

