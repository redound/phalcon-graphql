<?php

namespace PhalconGraphQL\Definition\ObjectTypeGroups;

use PhalconGraphQL\Definition\ObjectType;

interface ObjectTypeGroupInterface
{
    /**
     * @return ObjectType[]
     */
    public function getObjectTypes();
}