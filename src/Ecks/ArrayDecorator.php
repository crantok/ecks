<?php

namespace Ecks;

use ArrayIterator;

// ArrayDecorator (trait)
//
// Effectively implements IteratorAggregate, ArrayAccess and Countable but,
// being a trait, it can't explicitly state this. If you need these interfaces
// to be implemented by your class then you can do this:
//
//  class MyClass implements IteratorAggregate, ArrayAccess, Countable
//  {
//      use ArrayDecorator;
//      ....
//
// And then implement the abstract methods: &arrayRef() and asArray()
//
trait ArrayDecorator
{
    // Allow the Array decorator to get a reference to the internal array or
    // array-like thing.
    abstract protected function &arrayRef();

    // Provide public read access to a representation of the class as a "native"
    // PHP array. Intended to enable client code to make use of PHP's global
    // array functions.
    abstract public function asArray();


    public function getIterator() {
        $internal_array = $this->arrayRef();
        return new ArrayIterator( $internal_array ? $internal_array : [] );
    }


    public function offsetExists( $offset )
    {
        return array_key_exists( $offset, $this->arrayRef() );
    }

    public function offsetGet( $offset )
    {
        return $this->arrayRef()[$offset];
    }

    public function offsetSet( $offset, $value )
    {
        $internal_array = &$this->arrayRef();

        // Check for a Null offset because $array[] not the same as $array[null]
        if (is_null($offset)) {
            $internal_array[] = $value;
        } else {
            $internal_array[$offset] = $value;
        }
    }

    public function offsetUnset( $offset )
    {
        unset( $this->arrayRef()[$offset] );
    }

    public function count()
    {
        return count($this->arrayRef());
    }
}
