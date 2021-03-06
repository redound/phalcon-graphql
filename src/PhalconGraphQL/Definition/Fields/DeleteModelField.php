<?php

namespace PhalconGraphQL\Definition\Fields;

use PhalconGraphQL\Core;
use PhalconGraphQL\Definition\InputField;
use PhalconGraphQL\Definition\Types;
use PhalconGraphQL\Resolvers\DeleteModelResolver;

class DeleteModelField extends ModelField
{
    public function __construct($model=null, $name=null, $type=null)
    {
        if($name === null){

            $modelName = ucfirst(Core::getShortClass($model));
            $name = 'delete' . $modelName;
        }

        parent::__construct($model, $name, Types::BOOLEAN);

        $this
            ->resolver(DeleteModelResolver::class)
            ->arg(InputField::factory('id', Types::ID)->nonNull());
    }
}