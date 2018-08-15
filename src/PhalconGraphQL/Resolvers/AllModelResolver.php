<?php

namespace PhalconGraphQL\Resolvers;

use PhalconGraphQL\Definition\Fields\Field;

class AllModelResolver extends ModelResolver
{
    use \ModelQueryTrait;
    use \AllModelTrait;

    public function resolve($source, array $args, Field $field)
    {
        return $this->_all($args, $field);
    }
}