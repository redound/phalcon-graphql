<?php

namespace PhalconGraphQL\Definition\Fields;

use PhalconGraphQL\Core;
use PhalconGraphQL\Definition\InputField;
use PhalconGraphQL\Definition\Types;
use PhalconGraphQL\Resolvers\CreateModelResolver;

class CreateModelField extends ModelField
{
    public function __construct($model=null, $name=null, $returnType=null, $inputType=null, $description=null)
    {
        $modelName = ucfirst(Core::getShortClass($model));

        if($name === null){
            $name = 'create' . $modelName;
        }

        if($inputType === null){
            $inputType = Types::addCreateInput($modelName);
        }

        $this
            ->resolver(CreateModelResolver::class)
            ->arg(InputField::factory('input', $inputType)->nonNull());

        parent::__construct($model, $name, $returnType, $description);
    }

    public static function factory($model=null, $name=null, $returnType=null, $inputType=null, $description=null)
    {
        return new CreateModelField($model, $name, $returnType, $inputType, $description);
    }
}