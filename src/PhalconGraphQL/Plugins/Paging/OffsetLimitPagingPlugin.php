<?php

namespace PhalconGraphQL\Plugins\Paging;

use Phalcon\DiInterface;
use PhalconGraphQL\Definition\Fields\AllModelField;
use PhalconGraphQL\Definition\Fields\Field;
use PhalconGraphQL\Definition\Fields\ModelField;
use PhalconGraphQL\Definition\Fields\RelationModelField;
use PhalconGraphQL\Definition\InputField;
use PhalconGraphQL\Definition\ObjectType;
use PhalconGraphQL\Plugins\Plugin;
use Phalcon\Mvc\Model\Query\BuilderInterface as QueryBuilder;

class OffsetLimitPagingPlugin extends Plugin
{
    public function beforeBuildField(Field $field, ObjectType $objectType, DiInterface $di)
    {
        if(!($field instanceof AllModelField) && !($field instanceof RelationModelField)) {
            return;
        }

        $field
            ->arg(InputField::int('offset'))
            ->arg(InputField::int('limit'));
    }

    public function modifyAllQuery(QueryBuilder $query, $args, Field $field)
    {
        $offset = isset($args['offset']) && !empty($args['offset']) ? (int)$args['offset'] : null;
        $limit = isset($args['limit']) && !empty($args['limit']) ? (int)$args['limit'] : null;

        if($offset !== null){
            $query->offset($offset);
        }

        if($limit !== null){
            $query->limit($limit);
        }
    }

    public function modifyRelationOptions($options, $source, $args, Field $field)
    {
        $offset = isset($args['offset']) && !empty($args['offset']) ? (int)$args['offset'] : null;
        $limit = isset($args['limit']) && !empty($args['limit']) ? (int)$args['limit'] : null;

        if ($offset !== null) {
            $options['offset'] = $offset;
        }

        if ($limit !== null) {
            $options['limit'] = $limit;
        }

        return $options;
    }
}