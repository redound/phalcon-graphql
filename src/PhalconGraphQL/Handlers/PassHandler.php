<?php

namespace PhalconGraphQL\Handlers;

class PassHandler
{
    public function __call($name, $arguments)
    {
        list($source) = $arguments;
        return $source;
    }
}