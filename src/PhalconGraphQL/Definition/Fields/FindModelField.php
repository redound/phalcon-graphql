<?php

namespace PhalconGraphQL\Definition\Fields;

use PhalconGraphQL\Core;
use PhalconGraphQL\Definition\InputField;
use PhalconGraphQL\Definition\Types;
use PhalconGraphQL\Resolvers\FindModelResolver;

class FindModelField extends ModelField
{
    public function __construct($model=null, $name=null, $type=null)
    {
        if($name === null){
            $name = 'find' . ucfirst(Core::getShortClass($model));
        }

        parent::__construct($model, $name, $type);

        $this
            ->resolver(FindModelResolver::class)
            ->arg(InputField::factory('id', Types::ID)->nonNull());
    }
}