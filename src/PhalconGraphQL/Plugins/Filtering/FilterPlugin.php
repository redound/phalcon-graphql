<?php

namespace PhalconGraphQL\Plugins\Filtering;

use Phalcon\DiInterface;
use PhalconGraphQL\Definition\Fields\AllModelField;
use PhalconGraphQL\Definition\Fields\Field;
use PhalconGraphQL\Definition\InputField;
use PhalconGraphQL\Definition\InputObjectType;
use PhalconGraphQL\Definition\Schema;
use PhalconGraphQL\Plugins\Plugin;
use Phalcon\Mvc\Model\Query\BuilderInterface as QueryBuilder;


class FilterPlugin extends Plugin
{
    public function beforeBuildSchema(Schema $schema, DiInterface $di)
    {
        $schema->inputObject(InputObjectType::factory('Filter')
            ->field(InputField::string('field', 'The field to apply the filter to'))
            ->field(InputField::string('value', 'The value to filter'))
        );
    }

    public function beforeBuildField(Field $field, DiInterface $di)
    {
        if($field instanceof AllModelField) {

            $field
                ->arg(InputField::factory('filters', 'Filter')->isList());
        }
    }

    public function modifyAllQuery(QueryBuilder $query, $args, Field $field)
    {
        if($field instanceof AllModelField) {

            $filters = isset($args['filters']) ? $args['filters'] : [];

            foreach($filters as $filter) {

                $field = $filter['field'];
                $value = $filter['value'];

                if ($field !== null) {
                    $query->andWhere('[' . $field . '] = ?1', [1 => $value]);
                }
            }
        }
    }
}