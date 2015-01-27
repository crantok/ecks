<?php

namespace Rpb\Adapters;


use Ecks\KeyValuePair;
use Ecks\ArrayDecorator;


class Ecks extends ArrayDecorator
{
    private $thing = NULL;
    protected function decoratedArray() { return $this->thing; }

    function __construct( $thing )
    {
        $this->thing = $thing;
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
}
