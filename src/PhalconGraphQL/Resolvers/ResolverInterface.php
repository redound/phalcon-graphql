<?php

namespace PhalconGraphQL\Resolvers;

use PhalconGraphQL\Definition\Fields\Field;

interface ResolverInterface
{
    public function resolve($source, $args, Field $field);
}