<?php

namespace PhalconGraphQL\Definition\ObjectTypeGroups;

use Phalcon\DiInterface;
use PhalconGraphQL\Definition\ObjectType;
use PhalconGraphQL\Definition\Schema;

interface ObjectTypeGroupInterface
{
    /**
     * @return ObjectType[]
     */
    public function getObjectTypes();

    public function allow($roles);

    public function deny($roles);

    public function allowObject($objectTypeName, $roles);

    public function denyObject($objectTypeName, $roles);

    public function build(Schema $schema, DiInterface $di);
}