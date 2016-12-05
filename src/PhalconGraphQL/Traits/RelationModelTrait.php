<?php

use PhalconGraphQL\Definition\Fields\Field;

trait RelationModelTrait
{
    public function _relation($source, $args, Field $field)
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