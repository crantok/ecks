<?php

namespace Ecks;


use Ecks\KeyValuePair;
use Ecks\ArrayDecorator;


class Ecks extends ArrayDecorator
{
    private $thing = NULL;
    protected function decoratedArray() { return $this->thing; }

    private $thingAsArrayMethodName = NULL;
    protected function asArrayMethodName() { return $this->thingAsArrayMethodName; }


    function __construct( $thing, $as_array_method_name=NULL )
    {
        $this->thing = $thing;
        $this->thingAsArrayMethodName = $as_array_method_name;
    }


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

        $this->thing = $results;
        return $this;
    }

    // Unlike PHP's array_diff and array_udiff functions, this method is NOT
    // order dependent
    function diff( $other, $callback )
    {
        $raw_other = is_array( $other ) ? $other : $other->asArray();

        $results = [];


    }
}
