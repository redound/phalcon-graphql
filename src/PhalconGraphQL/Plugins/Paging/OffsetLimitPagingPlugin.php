<?php

namespace PhalconGraphQL\Plugins\Paging;

use Phalcon\DiInterface;
use PhalconGraphQL\Definition\Fields\Field;
use PhalconGraphQL\Definition\Fields\ModelField;
use PhalconGraphQL\Definition\InputField;
use PhalconGraphQL\Definition\ObjectType;
use PhalconGraphQL\Plugins\Plugin;
use Phalcon\Mvc\Model\Query\BuilderInterface as QueryBuilder;

class OffsetLimitPagingPlugin extends Plugin
{
    public function beforeBuildField(Field $field, ObjectType $objectType, DiInterface $di)
    {
        if(!($field instanceof ModelField) || !$field->getIsList()) {
            return;
        }

        $field
            ->arg(InputField::int('offset'))
            ->arg(InputField::int('limit'));
    }

    public function modifyAllQuery(QueryBuilder $query, $args, Field $field)
    {
        if(!($field instanceof ModelField)) {
            return;
        }

        $offset = isset($args['offset']) && !empty($args['offset']) ? (int)$args['offset'] : null;
        $limit = isset($args['limit']) && !empty($args['limit']) ? (int)$args['limit'] : null;

        if($offset !== null){
            $query->offset($offset);
        }

        if($limit !== null){
            $query->limit($limit);
        }
    }
}