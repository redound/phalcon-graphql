<?php

namespace PhalconGraphQL;

class Core extends \PhalconApi\Core
{
    public static function getShortClass($class){

        $path = explode('\\', $class);
        return array_pop($path);
    }
}