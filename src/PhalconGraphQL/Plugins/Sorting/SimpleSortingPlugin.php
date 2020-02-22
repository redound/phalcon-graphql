<?php

namespace PhalconGraphQL\Plugins\Sorting;

use Phalcon\Di\DiInterface;
use PhalconGraphQL\Core;
use PhalconGraphQL\Definition\EnumType;
use PhalconGraphQL\Definition\EnumTypeValue;
use PhalconGraphQL\Definition\Fields\AllModelField;
use PhalconGraphQL\Definition\Fields\Field;
use PhalconGraphQL\Definition\Fields\ModelField;
use PhalconGraphQL\Definition\Fields\RelationModelField;
use PhalconGraphQL\Definition\InputField;
use PhalconGraphQL\Definition\ObjectType;
use PhalconGraphQL\Definition\Schema;
use PhalconGraphQL\Definition\Types;
use PhalconGraphQL\Plugins\Plugin;
use Phalcon\Mvc\Model\Query\BuilderInterface as QueryBuilder;

class SimpleSortingPlugin extends Plugin
{
    const DIRECTION_ASC = 'ASC';
    const DIRECTION_DESC = 'DESC';

    public function beforeBuildField(Field $field, ObjectType $objectType, DiInterface $di)
    {
        if(!($field instanceof AllModelField) && !($field instanceof RelationModelField && $field->getIsList())) {
            return;
        }

        if(!$this->schema->hasEnum('SortDirection')) {

            $this->schema->enum(EnumType::factory('SortDirection')
                ->value(self::DIRECTION_ASC, self::DIRECTION_ASC, 'Sort ascending')
                ->value(self::DIRECTION_DESC, self::DIRECTION_DESC, 'Sort descending')
            );
        }

        $type = $field->getType();
        $fieldEnumName = $type . 'SortField';

        /** @var ObjectType $fieldObjectType */
        $fieldObjectType = $this->schema->findObjectType($type);
        if(!$fieldObjectType){
            return;
        }

        if(!$this->schema->hasEnum($fieldEnumName)) {

            $enum = EnumType::factory($fieldEnumName);

            $this->createEnumValues($fieldObjectType->getFields(), $enum);

            $this->schema->enum($enum);
        }

        $field
            ->arg(InputField::factory('sortField', $fieldEnumName))
            ->arg(InputField::factory('sortDirection', 'SortDirection')
                ->defaultValue(self::DIRECTION_ASC)
            );
    }

    public function modifyAllQuery(QueryBuilder $query, array $args, Field $field, $isCount)
    {
        $model = Core::getShortClass($field->getModel());
        $sortField = isset($args['sortField']) && !empty($args['sortField']) ? $args['sortField'] : null;
        $sortDirection = isset($args['sortDirection']) && !empty($args['sortDirection']) ? $args['sortDirection'] : self::DIRECTION_ASC;

        if($sortField !== null && !$isCount){
            $this->modifyAllQueryForSort($query, $sortField, $sortDirection, $model, $field);
        }
    }

    public function modifyRelationOptions($options, $source, array $args, Field $field, $isCount)
    {
        $sortField = isset($args['sortField']) && !empty($args['sortField']) ? $args['sortField'] : null;
        $sortDirection = isset($args['sortDirection']) && !empty($args['sortDirection']) ? $args['sortDirection'] : self::DIRECTION_ASC;

        if($sortField !== null && !$isCount){
            $options = $this->modifyRelationOptionsForSort($options, $sortField, $sortDirection, $field);
        }

        return $options;
    }


    protected function createEnumValues($fields, EnumType $enum)
    {
        $schemaScalars = array_map(function($type){ return $type->name; }, $this->schema->getScalarTypes());
        $scalarTypes = array_merge(Types::scalars(), $schemaScalars);

        /** @var EnumType $enumType */
        foreach($this->schema->getEnumTypes() as $enumType){
            $scalarTypes[] = $enumType->getName();
        }

        /** @var Field $typeField */
        foreach($fields as $typeField){

            if($typeField->getIsList() || !in_array($typeField->getType(), $scalarTypes)){
                continue;
            }

            $enum->value($typeField->getName(), $typeField->getName());
        }

        foreach($this->getExtraEnumValues($fields) as $value){

            $enum->addValue($value);
        }
    }

    protected function getExtraEnumValues($fields)
    {
        return [];
    }

    public function modifyAllQueryForSort(QueryBuilder $query, $sortField, $sortDirection, $modelName, Field $field)
    {
        $query->orderBy('[' . $modelName . '].[' . $sortField . '] ' . $sortDirection);
    }

    public function modifyRelationOptionsForSort($options, $sortField, $sortDirection, Field $field)
    {
        $order = isset($options['order']) && !empty($options['order']) ? $options['order'] . ', ' : '';
        $order .= $sortField . ' ' . $sortDirection;

        $options['order'] = $order;

        return $options;
    }
}