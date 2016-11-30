<?php

namespace PhalconGraphQL\Definition\Fields;

use PhalconGraphQL\Core;
use PhalconGraphQL\Resolvers\AllModelResolver;

class AllModelField extends ModelField
{
    public function __construct($model=null, $name=null, $type=null, $description=null, $embedMode=null)
    {
        if($name === null){
            $name = 'all' . ucfirst(Core::getShortClass($model));
        }

        parent::__construct($model, $name, $type, $description, $embedMode);

        $this
            ->resolver(AllModelResolver::class)
            ->isList()
            ->nonNull();
    }

    public static function factory($model=null, $name=null, $type=null, $description=null)
    {
        return new AllModelField($model, $name, $type, $description);
    }
}