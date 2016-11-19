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

    public function build(Schema $schema, DiInterface $di);
}