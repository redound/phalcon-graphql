<?php

namespace PhalconGraphQL\Plugins;

use Phalcon\Di\DiInterface;
use PhalconGraphQL\Definition\ObjectType;

interface ObjectTypePluginInterface
{
    public function beforeBuildObjectType(ObjectType $objectType, DiInterface $di);
    public function afterBuildObjectType(ObjectType $objectType, DiInterface $di);
}