<?php

namespace PhalconGraphQL;

class Core extends \PhalconApi\Core
{
    public static function getShortClass($class){

        if(method_exists($class, 'objectName')){
            return $class::objectName();
        }

        $path = explode('\\', $class);
        return array_pop($path);
    }
}