<?php

namespace PhalconGraphQL\Plugins;

use Phalcon\DiInterface;
use PhalconGraphQL\Definition\Fields\Field;
use PhalconGraphQL\Definition\ObjectType;
use PhalconGraphQL\Definition\Schema;

abstract class Plugin implements PluginInterface
{
    public function beforeBuildField(Field $field, DiInterface $di)
    {

    }

    public function afterBuildField(Field $field, DiInterface $di)
    {

    }

    public function beforeBuildObjectType(ObjectType $objectType, DiInterface $di)
    {

    }

    public function afterBuildObjectType(ObjectType $objectType, DiInterface $di)
    {

    }

    public function beforeBuildSchema(Schema $schema, DiInterface $di)
    {

    }

    public function afterBuildSchema(Schema $schema, DiInterface $di)
    {

    }
}