<?php declare(strict_types=1);

use makeUp\lib\Module;
use PHPUnit\Framework\TestCase;
use makeUp\lib\Tools;

class AuthenticationTest extends TestCase
{
    private $auth; 
 
    protected function setUp() : void
    {
        $this->auth = Module::create('authentication');
    }
 
    protected function tearDown() : void
    {
        $this->auth = NULL;
    }
    

    public function testAuthenticate()
    {
        $token = Tools::createFormToken();
        $this->assertTrue($this->auth->authenticate($token, 'user', 'pass'));
        $this->assertFalse($this->auth->authenticate($token, 'user', 'asdfg'));
        $this->assertFalse($this->auth->authenticate($token, 'qwert', 'pass'));
    }
}
