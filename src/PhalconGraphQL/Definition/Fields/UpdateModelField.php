<?php

namespace PhalconGraphQL\Definition\Fields;

use PhalconGraphQL\Core;
use PhalconGraphQL\Definition\InputField;
use PhalconGraphQL\Definition\Types;
use PhalconGraphQL\Resolvers\UpdateModelResolver;

class UpdateModelField extends ModelField
{
    public function __construct($model=null, $name=null, $returnType=null, $inputType=null)
    {
        $modelName = ucfirst(Core::getShortClass($model));

        if($name === null){
            $name = 'update' . $modelName;
        }

        if($inputType === null){
            $inputType = Types::addUpdateInput($modelName);
        }

        parent::__construct($model, $name, $returnType);

        $this
            ->resolver(UpdateModelResolver::class)
            ->arg(InputField::factory('input', $inputType)->nonNull());
    }
}