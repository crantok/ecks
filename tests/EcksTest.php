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
        $this->assertEquals( [2,4,7], $x->asArray() );

        $x = ecks( [2,4,7] );
        $this->assertEquals( [2,4,7], $x->asArray() );

        $x = ecks( $x, "some rubbish" );
        $this->assertEquals( [2,4,7], $x->asArray() );

        $x = ecks( [2,4,7] );
        $x[1] = 5;
        $this->assertEquals( [2,5,7], $x->asArray() );

        $x = ecks( [2,4,7] )->map( function($val){return $val+1;});
        $this->assertEquals( [3,5,8], $x->asArray() );

        $x = ecks( [2,4,7] )->filter( function($val){return $val>3 ? $val+1 : NULL;});
        $this->assertEquals( [5,8], $x->asArray() );
        $this->assertEquals( 2, count($x) );
        $this->assertEquals( 2, $x->count() );
    }
}
