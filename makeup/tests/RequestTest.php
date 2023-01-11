<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use makeUp\lib\Request;

class RequestTest extends TestCase
{
    protected function setUp() : void
    {
    }
 
    protected function tearDown() : void
    {
    }
    

    public function testFilter()
    {
        $maliciousInput = [
            "param1" => "Schönes Wetter heute!<script>alert('XSS!');</script>"
        ];
        $filteredInput = Request::parseQuery($maliciousInput);
        foreach($filteredInput as $input) {
            $this->assertEquals(0, preg_match("/(<|>)/i", $input)); // Checking that "<" or ">" occur 0 times in the string
            $this->assertEquals(1, preg_match("/(&lt;|&gt;)/i", $input)); // Checking if "<" or ">" have been replaced with special chars.
        }
    }
}
