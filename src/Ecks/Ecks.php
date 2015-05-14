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



    // Return TRUE if an item passes the given test, else FALSE.
    //
    // Callback params: ( value, key, original array )
    //
    public function any( $callback )
    {
        foreach ( $this->thing as $key => $value ) {

            if ( $callback( $value, $key, $this->thing ) ) {
                return TRUE;
            }
        }
        return FALSE;
    }



    // Return the a KeyValuePair for the first item passing the recursively
    // applied truth iterator test.
    //
    // Recursion is applied where an element is Traversable, or is an array, or
    // returns one of the same through the supplied child method.
    //
    // Callback params: ( value, key, original array )
    //
    public function recursiveFind( $callback, $child_method=null)
    {
        $is_foreachable = function ( $collection ) {
            return is_array( $collection ) || $collection instanceof Traversable;
        };

        $rf = function ( $collection, $indent=0 ) use ( $callback, $child_method, &$rf, $is_foreachable )
        {
            foreach( $collection as $key => $value ) {

                $result = $callback( $value, $key, $collection );

                if ( $result ) {
                    return new KeyValuePair( $key, $value );
                }
                elseif ( $child_method && method_exists( $value, $child_method )  ) {
                    $child_result = $rf( $value->$child_method(), $indent+4 );
                    if ( $child_result ) { return $child_result; }
                }
                elseif ( $is_foreachable( $value ) ) {
                    $child_result = $rf( $value, $indent+4 );
                    if ( $child_result ) { return $child_result; }
                }
            }

            return NULL;
        };

        return $rf( $this->thing );
    }



    // // Unlike PHP's array_diff and array_udiff functions, this method is NOT
    // // order dependent
    // function diff( $other, $callback )
    // {
    //     $raw_other = is_array( $other ) ? $other : $other->asArray();
    //
    //     $results = [];
    //
    //     // TO DO: You know, like, write the method.
    // }
}
