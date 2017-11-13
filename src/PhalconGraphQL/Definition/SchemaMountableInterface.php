<?php

namespace PhalconGraphQL\Definition;

use Phalcon\DiInterface;

interface SchemaMountableInterface
{
    public function getEnumTypes();

    public function getObjectTypes();

    public function getUnionTypes();

    public function getInputObjectTypes();

    public function getObjectTypeGroups();

    public function getFields();

    public function getFieldGroups();

    public function build(Schema $schema, DiInterface $di);
}