<?php

namespace PhalconGraphQL\Plugins\Filtering;

use PhalconGraphQL\Core;
use PhalconGraphQL\Definition\Fields\Field;
use PhalconGraphQL\Plugins\Plugin;
use Phalcon\Mvc\Model\Query\BuilderInterface as QueryBuilder;

class SoftDeletePlugin extends Plugin
{
    protected $deletedField;
    protected $deletedValue;

    public function __construct($deletedField = 'deleted', $deletedValue = 1)
    {
        $this->deletedField = $deletedField;
        $this->deletedValue = $deletedValue;
    }

    public function modifyAllQuery(QueryBuilder $query, $args, Field $field, $isCount)
    {
        $model = Core::getShortClass($field->getModel());

        $query->andWhere('[' . $model . '].[' . $this->deletedField . '] != ?1', [1 => $this->deletedValue]);
    }

    public function modifyRelationOptions($options, $source, $args, Field $field, $isCount)
    {
        $conditions = isset($options['conditions']) && !empty($options['conditions']) ? $options['conditions'] . ' AND ' : '';
        $bind = isset($options['bind']) && !empty($options['bind']) ? $options['bind'] : [];

        $conditions .= '[' . $this->deletedField . '] != :softDeleteValue:';
        $bind['softDeleteValue'] = $this->deletedValue;

        $options['conditions'] = $conditions;
        $options['bind'] = $bind;

        return $options;
    }
}