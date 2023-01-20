<?php declare(strict_types=1);

namespace makeUp\src;

class AccessDenied extends Module {

	public function __construct()
	{
	}

	protected function build(Request $request): string
	{
		return $this->render(Utils::errorMessage(Lang::get("access_denied")));
	}
}