<?php

namespace PhalconGraphQL\Resolvers;

use PhalconGraphQL\Definition\Fields\Field;

class CreateModelResolver extends ModelResolver
{
    use \ModelMutationTrait;
    use \CreateModelTrait;

    public function resolve($source, array $args, Field $field)
    {
        return $this->_create($args, $field);
    }
}