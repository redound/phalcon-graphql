<?php

namespace PhalconGraphQL\Handlers;

class EmptyHandler
{
    public function __call($name, $arguments)
    {
        return [];
    }
}