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

        $x = ecks( new PretendArray, "aBigPie" );
        $this->assertEquals( $x->asArray(), [2,4,7] );

        $x = ecks( [2,4,7] );
        $this->assertEquals( $x->asArray(), [2,4,7] );

        $x = ecks( $x, "some rubbish" );
        $this->assertEquals( $x->asArray(), [2,4,7] );

        $x = ecks( [2,4,7] );
        $x[1] = 5;
        $this->assertEquals( $x->asArray(), [2,5,7] );

        $x = ecks( [2,4,7] )->map( function($val){return $val+1;});
        $this->assertEquals( $x->asArray(), [3,5,8] );

        $x = ecks( [2,4,7] )->filter( function($val){return $val>3 ? $val+1 : NULL;});
        $this->assertEquals( $x->asArray(), [5,8] );
    }
}
