<?php

namespace PhalconGraphQL\Plugins\Filtering;

use Phalcon\DiInterface;
use PhalconGraphQL\Core;
use PhalconGraphQL\Definition\EnumType;
use PhalconGraphQL\Definition\Fields\AllModelField;
use PhalconGraphQL\Definition\Fields\Field;
use PhalconGraphQL\Definition\Fields\RelationModelField;
use PhalconGraphQL\Definition\InputField;
use PhalconGraphQL\Definition\InputObjectType;
use PhalconGraphQL\Definition\ObjectType;
use PhalconGraphQL\Definition\Types;
use PhalconGraphQL\Plugins\Plugin;
use Phalcon\Mvc\Model\Query\BuilderInterface as QueryBuilder;


class FilterPlugin extends Plugin
{
    protected $_createdFilterTypes = [];

    public function beforeBuildField(Field $field, ObjectType $objectType, DiInterface $di)
    {
        if(!($field instanceof AllModelField) && !($field instanceof RelationModelField && $field->getIsList())) {
            return;
        }

        $type = $field->getType();
        $filterTypeName = $type . 'Filter';

        /** @var ObjectType $fieldObjectType */
        $fieldObjectType = $this->schema->findObjectType($type);
        if(!$fieldObjectType){
            return;
        }

        if(!in_array($filterTypeName, $this->_createdFilterTypes)) {

            $inputType = InputObjectType::factory($filterTypeName);

            $schemaScalars = array_map(function($type){ return $type->name; }, $this->schema->getScalarTypes());
            $scalarTypes = array_merge(Types::scalars(), $schemaScalars);

            /** @var EnumType $enumType */
            foreach($this->schema->getEnumTypes() as $enumType){
                $scalarTypes[] = $enumType->getName();
            }

            /** @var Field $typeField */
            foreach($fieldObjectType->getFields() as $typeField){

                if($typeField->getIsList() || !in_array($typeField->getType(), $scalarTypes)){
                    continue;
                }

                $inputType->field(InputField::factory($typeField->getName(), $typeField->getType()));
            }

            $this->schema->inputObject($inputType);
            $inputType->build($this->schema, $di);

            $this->_createdFilterTypes[] = $inputType;
        }

        $field
            ->arg(InputField::factory('filter', $filterTypeName));
    }

    public function modifyAllQuery(QueryBuilder $query, $args, Field $field, $isCount)
    {
        $model = Core::getShortClass($field->getModel());
        $filter = isset($args['filter']) ? $args['filter'] : null;

        if($filter === null) {
            return;
        }

        foreach($filter as $field => $value) {

            $valueKey = 'filterValue_' . $field;
            $query->andWhere('[' . $model . '].[' . $field . '] = :'.$valueKey.':', [$valueKey => $value]);
        }
    }

    public function modifyRelationOptions($options, $source, $args, Field $field, $isCount)
    {
        $filter = isset($args['filter']) ? $args['filter'] : null;

        if($filter === null || count(array_keys($filter)) == 0) {
            return;
        }

        $conditions = isset($options['conditions']) && !empty($options['conditions']) ? $options['conditions'] . ' AND ' : '';
        $bind = isset($options['bind']) && !empty($options['bind']) ? $options['bind'] : [];

        $filterConditions = [];

        foreach($filter as $field => $value) {

            $varName = 'filter' . $field . 'Value';
            $filterConditions[] = '[' . $field . '] = :' . $varName . ':';
            $bind[$varName] = $value;
        }

        $conditions .= '(' . implode(' AND ', $filterConditions) . ')';

        $options['conditions'] = $conditions;
        $options['bind'] = $bind;

        return $options;
    }
}