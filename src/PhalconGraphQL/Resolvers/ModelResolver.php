<?php

namespace PhalconGraphQL\Resolvers;

use PhalconGraphQL\Definition\Fields\Field;
use PhalconGraphQL\Definition\Fields\ModelField;

abstract class ModelResolver extends Resolver
{
    protected function getModel(Field $field){

        if(!($field instanceof ModelField)){
            throw new \Exception("Field " . $field->getName() . " should be an instance of ModelField");
        }

        return $field->getModel();
    }
}