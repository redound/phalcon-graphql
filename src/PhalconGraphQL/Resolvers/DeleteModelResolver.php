<?php

namespace PhalconGraphQL\Resolvers;

use PhalconGraphQL\Definition\Fields\Field;

class DeleteModelResolver extends ModelResolver
{
    use \ModelMutationTrait;
    use \DeleteModelTrait;

    public function resolve($source, $args, Field $field)
    {
        return $this->_delete($args, $field);
    }
}