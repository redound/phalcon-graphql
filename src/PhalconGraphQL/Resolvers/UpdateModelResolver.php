<?php

namespace PhalconGraphQL\Resolvers;

use PhalconGraphQL\Definition\Fields\Field;

class UpdateModelResolver extends ModelResolver
{
    use \ModelMutationTrait;
    use \UpdateModelTrait;

    public function resolve($source, $args, Field $field)
    {
        return $this->_update($field, $args['input']);
    }
}