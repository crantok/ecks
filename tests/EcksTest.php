<?php

require_once "__DIR__/../vendor/autoload.php";

use Ecks\Ecks;


class PretendArray
{
    function aBigPie() { return [2,4,7]; }
}


class EcksTest extends PHPUnit_Framework_TestCase
{

    function testEcks()
    {
        $x = ecks([]);

        $y = ecks( new PretendArray, "aBigPie" )->asArray();
        $this->assertEquals( $y, [2,4,7] );

        $z = ecks( [2,4,7] )->asArray();
        $this->assertEquals( $z, [2,4,7] );
    }
}
