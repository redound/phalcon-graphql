<?php

namespace PhalconGraphQL\Resolvers;

use PhalconGraphQL\Definition\ObjectType;
use PhalconGraphQL\Definition\Schema;
use PhalconGraphQL\Mvc\Plugin;

abstract class Resolver extends Plugin implements ResolverInterface
{
    /** @var Schema */
    protected $schema;

    /** @var ObjectType */
    protected $objectType;

    /**
     * @param Schema $schema
     */
    public function setSchema($schema)
    {
        $this->schema = $schema;
    }

    /**
     * @param ObjectType $objectType
     */
    public function setObjectType($objectType)
    {
        $this->objectType = $objectType;
    }
}