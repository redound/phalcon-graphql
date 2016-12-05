<?php

namespace PhalconGraphQL\Plugins;

use Phalcon\DiInterface;
use PhalconGraphQL\Definition\Fields\Field;
use PhalconGraphQL\Definition\ObjectType;

interface FieldPluginInterface
{
    public function beforeBuildField(Field $field, ObjectType $objectType, DiInterface $di);
    public function afterBuildField(Field $field, ObjectType $objectType, DiInterface $di);
}