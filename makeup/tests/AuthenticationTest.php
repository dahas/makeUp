<?php declare(strict_types=1);

use makeUp\src\Module;
use PHPUnit\Framework\TestCase;
use makeUp\src\Utils;
use makeUp\lib\Auth;

class AuthenticationTest extends TestCase
{
    private $auth; 
    private $authentication; 
 
    protected function setUp() : void
    {
        $this->authentication = Module::create('Authentication');
        $this->auth = new Auth();
    }
 
    protected function tearDown() : void
    {
        $this->authentication = NULL;
    }
    

    public function testAuthenticate()
    {
        $token = $this->auth->createFormToken("auth");
        $this->assertTrue($this->authentication->authorize($token, 'user', 'pass'));
        $this->assertFalse($this->authentication->authorize($token, 'user', 'asdfg'));
        $this->assertFalse($this->authentication->authorize($token, 'qwert', 'pass'));
    }
}
