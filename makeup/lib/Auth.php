<?php declare(strict_types=1);

namespace makeUp\lib;

use makeUp\src\Session;
use makeUp\src\Utils;


final class Auth {

	private $filePath;


	public function __construct()
	{
		$this->filePath = dirname(__DIR__, 1) . "/users.txt";
	}


	public static function check(): bool
	{
		return Session::get("logged_in") ?? false;
	}


	public function register(string $username, string $password): bool
	{
		if (self::check())
			return false;

		if ($this->userExists($username))
			return false;

		$file = @fopen($this->filePath, "a+");
		if (!$file)
			return false;

		$userdata = $username . ":" . password_hash($password, PASSWORD_BCRYPT) . ":END";
		if(fwrite($file, $userdata . PHP_EOL)) {
			fclose($file);
			return true;
		}

		return false;
	}


	/**
	 * Use this function to grant or deny a user access to protected features and content.
	 * @param bool $verified
	 * @return void
	 */
	public function verified(bool $verified): void
	{
		session_regenerate_id(true);
		Session::set("logged_in", $verified);
	}


	public function authorized(string $token, string $un, string $pw): bool
	{
		$userData = $this->userExists($un);

		if (!$userData)
			return false;

		$username = $userData[0];
		$hash = $userData[1];
		$validPw = password_verify($pw, $hash);

		return $this->checkFormToken("auth", $token) && $username === $un && $validPw;
	}


	public function userExists(string $username): array |false
	{
		$file = @fopen($this->filePath, "r");
		if (!$file)
			return false;

		while (($line = fgets($file, 4096)) !== FALSE) {
			$dataArr = explode(":", $line);
			if ($dataArr[0] == $username) {
				return $dataArr;
			}
		}

		fclose($file);

		return false;
	}

    public function createFormToken(string $name): string
    {
        $expSecs = 5; // Token expires after this amount of seconds
        $timestamp = time();
        if ($timestamp >= Session::get($name . "_token_expires")) {
            $token = sha1($timestamp . random_int(1000, 9999));
            Session::set($name . "_token", $token);
            Session::set($name . "_token_expires", $timestamp + $expSecs);
            return $token;
        } else {
            return Session::get($name . "_token");
        }
    }

    public function checkFormToken(string $name, string $token): bool
    {
        $valid = $token == Session::get($name . "_token");
        if (time() >= Session::get($name . "_token_expires")) {
            Session::clear($name . "_token");
        }
        return $valid;
    }

}