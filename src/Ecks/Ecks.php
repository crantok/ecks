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


    // *** A note on building array results ***
    //
    // Callback functions may return a KeyValuePair in order to control the
    // *key* as well as the value in the result array. This means that client
    // code can preserve the original array keys in the result, or use another
    // key scheme if desired.
    //
    // A side effect of this is that methods that would normally copy values
    // verbatim from the original array can instead add transformed values to
    // the result.
    //
    // ***


    // Build an array that may contain any values, and that maps 1:1 to
    // the input array.
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

    // Build an array containing values for which the callback function returns
    // a truthy result.
    //
    // Note: A KeyValuePair returned by the callback function is considered a
    // truthy result, even if the value in the KeyValuePair is falsey.
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
                $results[] = $value;
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
