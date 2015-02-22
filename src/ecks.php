<?php

use Ecks\Ecks;


function ecks( $thing, $as_array_method_name=NULL )
{
    if ( $thing instanceof Ecks ) {
        return $thing;
    }
    else {
        return new Ecks($thing, $as_array_method_name );
    }
}
