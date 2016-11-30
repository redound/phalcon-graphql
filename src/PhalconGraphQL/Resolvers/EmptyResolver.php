<?php

namespace PhalconGraphQL\Resolvers;

use PhalconGraphQL\Definition\Fields\Field;

class EmptyResolver implements ResolverInterface
{
    public function resolve($source, $args, Field $field)
    {
        return [];
    }
}