<?php

namespace PhalconGraphQL\Definition\Fields;

use Phalcon\DiInterface;
use PhalconGraphQL\Core;
use PhalconGraphQL\Definition\InputField;
use PhalconGraphQL\Definition\Schema;
use PhalconGraphQL\Resolvers\AllModelResolver;

class AllModelField extends ModelField
{
    public function __construct($model=null, $name=null, $type=null)
    {
        if($name === null){
            $name = 'all' . ucfirst(Core::getShortClass($model)) . 's';
        }

        parent::__construct($model, $name, $type);

        $this
            ->resolver(AllModelResolver::class)
            ->isList()
            ->nonNull();
    }
}