<?php declare(strict_types = 1);

namespace makeUp\lib;

class RouteNotFound extends Module {

	public function __construct()
	{
	}

	protected function build(): string
	{
		return Utils::errorMessage(Lang::get("route_not_found"));
	}
}
