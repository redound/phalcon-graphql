<?php

namespace PhalconGraphQL\Definition\Fields;

use PhalconGraphQL\Resolvers\RelationModelResolver;

class RelationModelField extends ModelField
{
    public function __construct($model=null, $name=null, $type=null)
    {
        parent::__construct($model, $name, $type);

        $this->resolver(RelationModelResolver::class);
    }
}