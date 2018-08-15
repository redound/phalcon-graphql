<?php

namespace PhalconGraphQL\Resolvers;

use PhalconGraphQL\Definition\Fields\Field;
use PhalconGraphQL\Definition\ObjectType;
use PhalconGraphQL\Definition\Schema;
use PhalconGraphQL\Mvc\Plugin;

class Resolver extends Plugin implements ResolverInterface
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

    public function resolve($source, array $args, Field $field)
    {
        $fieldName = $field->getName();
        $property = null;

        if (is_array($source) || $source instanceof \ArrayAccess) {
            if (isset($source[$fieldName])) {
                $property = $source[$fieldName];
            }
        } else if (is_object($source)) {
            if (isset($source->{$fieldName})) {
                $property = $source->{$fieldName};
            }
        }

        return $property instanceof \Closure ? $property($source, $args, $field) : $property;
    }
}