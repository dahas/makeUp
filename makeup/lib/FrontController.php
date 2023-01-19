<?php declare(strict_types=1);

namespace makeUp\lib;
use makeUp\App;

class FrontController {


    public function __construct()
    {
        Session::start();
    }

    public function handle(): void
    {
        $App = new App();
        $App->execute(new Request());
    }

}