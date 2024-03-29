<?php declare(strict_types=1);

namespace makeUp\src;

class RouteNotFound extends Module {

	public function __construct()
	{
	}

	protected function build(Request $request): string
	{
		return $this->render(Utils::errorMessage(Lang::get("route_not_found")));
	}
}