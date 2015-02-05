<?php

namespace Ecks;

use IteratorAggregate;
use ArrayAccess;


class Ecks implements IteratorAggregate, ArrayAccess
{
    use ArrayDecorator;

    private $thing = NULL;
    protected function internalArray() { return $this->thing; }

    private $thingAsArrayMethodName = NULL;


    function __construct( $thing, $as_array_method_name=NULL )
    {
        $this->setThing( $thing, $as_array_method_name );
    }

    private function setThing( $thing, $as_array_method_name=NULL )
    {
        $this->thing = $thing;
        $this->thingAsArrayMethodName = $as_array_method_name;
    }

    public function asArray()
    {
        if ( $this->thingAsArrayMethodName ) {
            $method_name = $this->thingAsArrayMethodName;
            return $this->thing->$method_name();
        }
        elseif ( is_array( $this->thing) ) {
            return $this->thing;
        }
        else {
            throw LogicException( 'Ecks::thing is not a PHP array, and there is no named method to get a PHP array from Ecks::thing');
        }
    }


    // And now for the fun stuff...

    function map( $callback )
    {
        $results = [];

        foreach ( $this->thing as $key => $value ) {

            $result = $callback( $value, $key, $this->thing );

            if ( $result instanceof KeyValuePair ) {
                $results[ $result->key ] = $result->value;
            }
            else {
                $results[] = $result;
            }
        }

        $this->setThing( $results );
        return $this;
    }

    // Unlike PHP's array_diff and array_udiff functions, this method is NOT
    // order dependent
    function diff( $other, $callback )
    {
        $raw_other = is_array( $other ) ? $other : $other->asArray();

        $results = [];

        // TO DO: You know, like, write the method.
    }
}
