<?php

use Ecks\Ecks;


function ecks( $thing, $as_array_method_name=NULL )
{
    return new Ecks($thing, $as_array_method_name );
}
