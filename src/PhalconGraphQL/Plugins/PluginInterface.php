<?php

namespace PhalconGraphQL\Plugins;

use Phalcon\DiInterface;
use PhalconGraphQL\Definition\Fields\Field;
use PhalconGraphQL\Definition\ObjectType;
use PhalconGraphQL\Definition\Schema;

interface PluginInterface extends FieldPluginInterface, ObjectTypePluginInterface, ModelHandlerPluginInterface
{
    public function beforeBuildSchema(Schema $schema, DiInterface $di);
    public function afterBuildSchema(Schema $schema, DiInterface $di);

    public function beforeResolve(Schema $schema, ObjectType $objectType, Field $field);

    public function setSchema(Schema $schema);
}