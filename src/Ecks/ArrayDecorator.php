<?php

namespace Ecks;

use ArrayIterator;

// ArrayDecorator (trait)
//
// Effectively implements IteratorAggregate and ArrayAccess but, being a trait,
// it can't explicitly state this. If you need these interfaces to be
// implemented by your class then you can do this:
//
//  class MyClass implements IteratorAggregate, ArrayAccess
//  {
//      use ArrayDecorator;
//      ....
//
// And then implement the abstract methods: internalArray() and asArray()
//
trait ArrayDecorator
{
    // Allow the Array decorator to see the internal array or array-like thing.
    abstract protected function internalArray();

    // Provide public read access to a representation of the class as a "native"
    // PHP array. Intended to enable client code to make use of PHP's global
    // array functions.
    abstract public function asArray();


    public function getIterator() {
        $internal_array = $this->internalArray();
        return new ArrayIterator( $internal_array ? $internal_array : [] );
    }


    public function offsetExists( $offset )
    {
        return array_key_exists( $this->internalArray(), $offset);
    }

    public function offsetGet( $offset )
    {
        return $this->internalArray()[$offset];
    }

    public function offsetSet( $offset, $value )
    {
        // Check for a Null offset because $array[] not the same as $array[null]
        if (is_null($offset)) {
            $this->internalArray()[] = $value;
        } else {
            $this->internalArray()[$offset] = $value;
        }
    }

    public function offsetUnset( $offset )
    {
        unset( $this->internalArray()[$offset] );
    }
}
