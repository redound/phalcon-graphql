<?php

namespace PhalconGraphQL\Resolvers;

use PhalconGraphQL\Definition\Fields\Field;

class RelationModelResolver extends ModelResolver
{
    use \ModelQueryTrait;
    use \RelationModelTrait;

    public function resolve($source, $args, Field $field)
    {
        return $this->_relation($args, $args, $field);
    }
}