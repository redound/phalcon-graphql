<?php

namespace PhalconGraphQL\Plugins;

use Phalcon\Di\DiInterface;
use PhalconGraphQL\Definition\Fields\Field;
use PhalconGraphQL\Definition\ObjectType;
use PhalconGraphQL\Definition\Schema;

interface ExecutionPluginInterface
{
    public function beforeResolveWithContext(Schema $schema, ObjectType $objectType, Field $field, DiInterface $di);
}
