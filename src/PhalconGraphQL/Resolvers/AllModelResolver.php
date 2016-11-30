<?php

namespace PhalconGraphQL\Resolvers;

use PhalconGraphQL\Definition\Fields\Field;

class AllModelResolver extends ModelResolver
{
    use \AllModelTrait;

    public function resolve($source, $args, Field $field)
    {
        return $this->_all($field);
    }
}