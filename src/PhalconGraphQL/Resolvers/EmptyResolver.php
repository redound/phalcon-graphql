<?php

namespace PhalconGraphQL\Resolvers;

use PhalconGraphQL\Definition\Field;

class EmptyResolver implements ResolverInterface
{
    public function resolve($source, $args, Field $field)
    {
        return [];
    }
}