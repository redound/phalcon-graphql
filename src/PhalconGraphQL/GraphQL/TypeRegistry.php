<?php

namespace PhalconGraphQL\GraphQL;

use GraphQL\Type\Definition\Type;

class TypeRegistry
{
    protected $_types = [];

    public function register($name, $type)
    {
        $this->_types[$name] = $type;
        return $this;
    }

    public function hasType($name)
    {
        return isset($this->_types[$name]);
    }

    public function resolve($name, $nonNull = false, $isList = false, $isNonNullList = false)
    {
        $type = isset($this->_types[$name]) ? $this->_types[$name] : null;

        if (!$type) {
            throw new \Exception("Could not resolve type '$name'");
        }

        if ($isList) {

            if ($isNonNullList) {
                $type = Type::nonNull($type);
            }

            $type = Type::listOf($type);
        }

        if ($nonNull) {
            $type = Type::nonNull($type);
        }

        return $type;
    }
}
