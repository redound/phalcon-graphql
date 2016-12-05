<?php

namespace PhalconGraphQL\Definition\Fields;

use PhalconGraphQL\Resolvers\RelationModelResolver;

class RelationModelField extends ModelField
{
    public function __construct($model=null, $name=null, $type=null, $description=null, $embedMode=null)
    {
        parent::__construct($model, $name, $type, $description, $embedMode);

        $this->resolver(RelationModelResolver::class);
    }

    public static function factory($model=null, $name=null, $type=null, $description=null)
    {
        return new RelationModelField($model, $name, $type, $description);
    }
}