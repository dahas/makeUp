<?php declare(strict_types=1);

use makeUp\src\Module;
use PHPUnit\Framework\TestCase;
use makeUp\src\Utils;
use makeUp\lib\Auth;

class AuthenticationTest extends TestCase
{
    private $auth;
 
    protected function setUp() : void
    {
        $this->auth = new Auth();
    }
 
    protected function tearDown() : void
    {
        $this->auth = NULL;
    }
    

    public function testAuthenticate()
    {
        $this->assertTrue($this->auth->authorize('user', 'pass'));
        $this->assertFalse($this->auth->authorize('user', 'asdfg'));
        $this->assertFalse($this->auth->authorize('qwert', 'pass'));
    }
}
