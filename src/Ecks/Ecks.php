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

    private function arrayCopy() {
        return ecks($this->thing)->map( function($v){return $v;}, TRUE );
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
    // Callback returns: a value or a KeyValuePair
    //
    // $preserve_keys_for_raw_values : Only applicable when callback returns a
    // plain value rather than a KeyValuePair. Preserves original keys in the
    // new array.
    //
    function map( $callback, $preserve_keys_for_raw_values=FALSE )
    {
        $results = [];

        foreach ( $this->thing as $key => $value ) {

            $result = $callback( $value, $key, $this->thing );

            if ( $result instanceof KeyValuePair ) {
                $results[ $result->key ] = $result->value;
            }
            elseif ( $preserve_keys_for_raw_values ) {
                $results[$key] = $result;
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
    // Callback returns: TRUE or FALSE or a KeyValuePair
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


    // Return the first key-value pair that passes the given test, else NULL.
    //
    // Callback params: ( value, key, original array )
    // Callback returns: TRUE or FALSE
    //
    public function find( $callback )
    {
        foreach ( $this->thing as $key => $value ) {

            if ( $callback( $value, $key, $this->thing ) ) {
                return new KeyValuePair( $key, $value );
            }
        }
        return NULL;
    }



    // Return TRUE if any element passes the given test, else FALSE.
    //
    // Callback params: ( value, key, original array )
    // Callback returns: TRUE or FALSE
    //
    public function any( $callback )
    {
        return $this->find( $callback ) ? TRUE : FALSE;
    }



    // Return TRUE if all elements pass the given test, else FALSE.
    //
    // Callback params: ( value, key, original array )
    // Callback returns: TRUE or FALSE
    //
    public function all( $callback )
    {
        // Implementation: Try to find an element that FAILS the given test.
        // Logically, if one element fails the test then it cannot be true
        // that all elements pass the test.

        return $this->find( function ($value,$key,$arr) use ($callback) {
            return ! $callback($value,$key,$arr);
        } ) ? FALSE : TRUE;
    }



    // Return the a KeyValuePair for the first item passing the recursively
    // applied truth iterator test.
    //
    // Recursion is applied where an element is Traversable, or is an array, or
    // returns one of the same through the supplied child method.
    //
    // Callback params: ( value, key, original array )
    // Callback returns: TRUE or FALSE
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



    // Build a new array sorted by the values returned by the callback.
    //
    // Values returned by the callback will be sorted using the PHP asort() fn.
    // The original array will be reordered in accordance with the sorted values.
    //
    // Array keys are discarded by default. This means that the [0] index will
    // return the first item in the result array. You can change this behaviour
    // by turning on $preserve_keys. This will reorder the array for the
    // purposes of foreach, etc, but the [0] will return whatever it did before.
    //
    // Callback params: ( value, key, original array )
    // Callback returns: A value for sort comparison.
    //
    // $preserve_keys : If true, change element ordering but do not change keys.
    //
    public function sortBy( $callback, $preserve_keys=FALSE )
    {
        $sortable = $this->arrayCopy()->map( $callback, TRUE )->asArray();
        asort( $sortable );

        $result = ecks($sortable)->map( function ( $val, $key ) use ( $preserve_keys ) {
            if ( $preserve_keys ) {
                return new KeyValuePair( $key, $this->thing[$key] );
            }
            else {
                return $this->thing[$key];
            }
        } );

        $this->setThing( $result->asArray() );
        return $this;
    }


    // Reduce the wrapped collection to a single value, starting from the first
    // value in the collection.
    //
    // Callback params: ( reduction, value, key, original array )
    // Callback returns: the reduced value
    //
    public function reduce( $callback, $reduction )
    {
        foreach ( $this->thing as $key => $value ) {
            $reduction = $callback( $reduction, $value, $key, $this->thing );
        }
        return $reduction;
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
