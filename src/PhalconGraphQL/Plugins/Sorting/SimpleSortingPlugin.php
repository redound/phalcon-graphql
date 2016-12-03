<?php

namespace PhalconGraphQL\Plugins\Sorting;

use Phalcon\DiInterface;
use PhalconGraphQL\Definition\EnumType;
use PhalconGraphQL\Definition\Fields\AllModelField;
use PhalconGraphQL\Definition\Fields\Field;
use PhalconGraphQL\Definition\InputField;
use PhalconGraphQL\Definition\Schema;
use PhalconGraphQL\Plugins\Plugin;
use Phalcon\Mvc\Model\Query\BuilderInterface as QueryBuilder;


class SimpleSortingPlugin extends Plugin
{
    const DIRECTION_ASC = 'ASC';
    const DIRECTION_DESC = 'DESC';

    public function beforeBuildSchema(Schema $schema, DiInterface $di)
    {
        $schema->enum(EnumType::factory('SortDirection')
            ->value(self::DIRECTION_ASC, self::DIRECTION_ASC, 'Sort ascending')
            ->value(self::DIRECTION_DESC, self::DIRECTION_DESC, 'Sort descending')
        );
    }

    public function beforeBuildField(Field $field, DiInterface $di)
    {
        if($field instanceof AllModelField) {

            $field
                ->arg(InputField::string('sortField'))
                ->arg(InputField::factory('sortDirection', 'SortDirection')
                    ->defaultValue(self::DIRECTION_ASC)
                );
        }
    }

    public function modifyAllQuery(QueryBuilder $query, $args, Field $field)
    {
        if($field instanceof AllModelField) {

            $field = isset($args['sortField']) && !empty($args['sortField']) ? $args['sortField'] : null;
            $direction = isset($args['sortDirection']) && !empty($args['sortDirection']) ? $args['sortDirection'] : self::DIRECTION_ASC;

            if($field !== null){
                $query->orderBy($field . ' ' . $direction);
            }
        }
    }
}