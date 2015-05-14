<?php

require_once "__DIR__/../vendor/autoload.php";

use Ecks\Ecks;
use Ecks\ArrayDecorator;


class PretendArray
{
    function aBigPie() { return [2,4,7]; }
}

class PretendTree implements IteratorAggregate, ArrayAccess, Countable
{
    use ArrayDecorator;

    function __construct( $children ) { $this->children = $children; }
    function anklebiters() { return $this->children; }
    function &arrayRef() { return $this->children; }
    function asArray() { return $this->children; }
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
        $this->assertEquals( [4,7], $x->asArray() );
        $this->assertEquals( 2, count($x) );
        $this->assertEquals( 2, $x->count() );

        $x = ecks( [2,5,[4,7,[4,6]],[1,[2,['x'=>'muppet'],3],4],5] )
        ->recursiveFind( function($val){return $val==='muppet';} );
        $this->assertInstanceOf( 'Ecks\KeyValuePair', $x );
        $this->assertEquals( 'x', $x->key );
        $this->assertEquals( 'muppet', $x->value );

        $x = ecks( [ new PretendTree([2,5, new PretendTree([4,7, new PretendTree([4,6])]), new PretendTree([1, new PretendTree([2, new PretendTree(['x'=>'muppet']),3]),4]),5]) ] )
        ->recursiveFind( function($val){return $val==='muppet';}, 'anklebiters' );
        $this->assertInstanceOf( 'Ecks\KeyValuePair', $x );
        $this->assertEquals( 'x', $x->key );
        $this->assertEquals( 'muppet', $x->value );

        $this->assertTrue( ecks([2,4,7])->any( function($val){return $val==2;} ) );
        $this->assertFalse( ecks([2,4,7])->any( function($val){return $val==13;} ) );

        $x = ecks([2,4,7])->find( function($val){return $val%4==0;} );
        $this->assertInstanceOf( 'Ecks\KeyValuePair', $x );
        $this->assertEquals( 1, $x->key );
        $this->assertEquals( 4, $x->value );

        $this->assertNull( ecks([2,4,7])->find( function($val){return $val==13;} ) );
    }
}
