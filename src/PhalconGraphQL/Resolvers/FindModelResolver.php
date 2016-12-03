<?php

namespace PhalconGraphQL\Resolvers;

use PhalconGraphQL\Definition\Fields\Field;

class FindModelResolver extends ModelResolver
{
    use \ModelQueryTrait;
    use \FindModelTrait;

    public function resolve($source, $args, Field $field)
    {
        return $this->_find($args, $field);
    }
}