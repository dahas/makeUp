<?php declare(strict_types=1);

use makeUp\lib\RQ;
use PHPUnit\Framework\TestCase;

class RQTest extends TestCase
{
    public function filterData() {
        return array(
            array('xyz<script>alert("xss!")</script>', 'xyzalert(&quot;xss!&quot;)'),
            array('abc%3Cscript%3Ealert%28%22xss%21%22%29%3C%2Fscript%3E', 'abcalert(&quot;xss!&quot;)'),
            array("<body onload=alert('something')>mod", 'mod')
        );
    }
 
    /**
     * @dataProvider filterData
     */
    public function testFilter($a, $b)
    {
        $_GET['mod'] = $a;
        RQ::init();
        $get = RQ::GET('mod');
        $this->assertSame($b, $get);
    }
}