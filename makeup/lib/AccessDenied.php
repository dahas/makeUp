<?php declare(strict_types = 1);

namespace makeUp\lib;

class AccessDenied extends Module {

	public function __construct()
	{
	}

	protected function build(): string
	{
		return Utils::errorMessage(Lang::get("access_denied"));
	}
}
