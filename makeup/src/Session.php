<?php declare(strict_types = 1);

namespace makeUp\src;


final class Session
{
	public static function start() : void
	{
		if (!isset($_SESSION))
			@session_start();
	}

	public static function get(string $key) : mixed
	{
		return $_SESSION[$key] ?? null;
	}

	public static function set(string $key, mixed $val) : void
	{
		$_SESSION[$key] = $val;
	}

	public static function clear(string $key) : void
	{
		$_SESSION[$key] = null;
		unset($_SESSION[$key]);
	}

	public static function destroy() : void
	{
		$_SESSION = null;
		unset($_SESSION);
	}

}

