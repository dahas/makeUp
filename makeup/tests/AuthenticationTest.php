<?php declare(strict_types=1);

use makeUp\lib\Module;
use PHPUnit\Framework\TestCase;
use makeUp\lib\Utils;

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
        $token = Utils::createFormToken("auth");
        $this->assertTrue($this->auth->authorized($token, 'user', 'pass'));
        $this->assertFalse($this->auth->authorized($token, 'user', 'asdfg'));
        $this->assertFalse($this->auth->authorized($token, 'qwert', 'pass'));
    }
}
