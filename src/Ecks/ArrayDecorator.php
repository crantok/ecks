<?php

namespace Ecks;


use IteratorAggregate;
use ArrayAccess;
use ArrayIterator;


abstract class ArrayDecorator implements IteratorAggregate, ArrayAccess
{
    abstract protected function decoratedArray();

    public function getIterator() {
        return new ArrayIterator( is_null($this->decoratedArray()) ? [] : $this->decoratedArray() );
    }

    public function offsetExists( $offset )
    {
        return array_key_exists( $this->decoratedArray(), $offset);
    }
    public function offsetGet( $offset )
    {
        return $this->decoratedArray()[$offset];
    }
    public function offsetSet( $offset, $value )
    {
        // Check for a Null offset because $array[] not the same as $array[null]
        if (is_null($offset)) {
            $this->decoratedArray()[] = $value;
        } else {
            $this->decoratedArray()[$offset] = $value;
        }
    }
    public function offsetUnset( $offset )
    {
        unset( $this->decoratedArray()[$offset] );
    }

    public function asArray()
    {
        return $this->decoratedArray();
    }
}
