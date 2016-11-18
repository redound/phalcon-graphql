<?php

namespace PhalconGraphQL\Handlers;

use PhalconGraphQL\Definition\Field;
use PhalconGraphQL\Definition\ObjectType;
use PhalconGraphQL\Definition\Schema;

class Handler
{
    /** @var ObjectType */
    protected $objectType;

    /** @var Schema */
    protected $schema;

    /**
     * @param ObjectType $objectType
     */
    public function setObjectType($objectType)
    {
        $this->objectType = $objectType;
    }

    /**
     * @param Schema $schema
     */
    public function setSchema($schema)
    {
        $this->schema = $schema;
    }

    public function __call($name, $arguments)
    {
        /** @var Field $field */
        list($source, $args, $field) = $arguments;

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
