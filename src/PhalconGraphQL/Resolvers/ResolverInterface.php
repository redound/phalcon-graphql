<?php

namespace PhalconGraphQL\Resolvers;

use PhalconGraphQL\Definition\Field;

interface ResolverInterface
{
    public function resolve($source, $args, Field $field);
}