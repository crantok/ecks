<?php

namespace Ecks;

use IteratorAggregate;
use ArrayAccess;
use Countable;


class Ecks implements IteratorAggregate, ArrayAccess, Countable
{
    use ArrayDecorator;

    private $thing = NULL;
    protected function &arrayRef() { return $this->thing; }

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

    // Build an array that may containing any values, and that maps 1:1 to
    // the input array.
    // The callback may return a KeyValuePair for more control over the results.
    //
    // Callback params: ( value, key, original array )
    //
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

    // Different from underscore filter() !
    //
    // Anything truthy returned from the callback is added to the results.
    // If the callback return TRUE, then TRUE is added to the results!
    // Just like with map(), a KeyValuePair can be used for more control.
    //
    // Callback params: ( value, key, original array )
    //
    function filter( $callback )
    {
        $results = [];

        foreach ( $this->thing as $key => $value ) {

            $result = $callback( $value, $key, $this->thing );

            if ( $result instanceof KeyValuePair ) {
                $results[ $result->key ] = $result->value;
            }
            elseif ( $result ) {
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
