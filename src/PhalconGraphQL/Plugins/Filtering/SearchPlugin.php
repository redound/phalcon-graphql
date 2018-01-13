<?php

namespace PhalconGraphQL\Plugins\Filtering;

use Phalcon\DiInterface;
use PhalconGraphQL\Core;
use PhalconGraphQL\Definition\Fields\AllModelField;
use PhalconGraphQL\Definition\Fields\Field;
use PhalconGraphQL\Definition\Fields\RelationModelField;
use PhalconGraphQL\Definition\InputField;
use PhalconGraphQL\Definition\ObjectType;
use PhalconGraphQL\Plugins\Plugin;
use Phalcon\Mvc\Model\Query\BuilderInterface as QueryBuilder;


class SearchPlugin extends Plugin
{
    protected $_shouldStartWithQuery = true;

    function __construct($shouldStartWithQuery = true)
    {
        $this->_shouldStartWithQuery = $shouldStartWithQuery;
    }

    public function beforeBuildField(Field $field, ObjectType $objectType, DiInterface $di)
    {
        if(!($field instanceof AllModelField) && !($field instanceof RelationModelField && $field->getIsList())) {
            return;
        }

        if(count($this->_getModelSearchFields($field->getModel())) > 0) {
            $field->arg(InputField::string('search'));
        }
    }

    public function modifyAllQuery(QueryBuilder $query, $args, Field $field, $isCount)
    {
        $model = $field->getModel();
        $modelShort = Core::getShortClass($model);

        $search = isset($args['search']) ? trim($args['search']) : null;
        $searchFields = $this->_getModelSearchFields($model);

        if($search === null || $search == '' || count($searchFields) == 0) {
            return;
        }

        $isFirst = true;
        $orQuery = '';

        $concatParams = $this->_shouldStartWithQuery ? ':searchQuery:, "%"' : '"%", :searchQuery:, "%"';
        $clauseValue = 'CONCAT(' . $concatParams . ')';

        foreach ($searchFields as $searchField) {

            if(!$isFirst) {
                $orQuery .= ' OR ';
            }

            $this->_modifyAllQueryForSearchField($query, $searchField, $modelShort);

            $orQuery .= $this->_getOrClause($searchField, $clauseValue, $modelShort);

            $isFirst = false;
        }

        $query->andWhere('(' . $orQuery . ')', ['searchQuery' => $search]);
    }

    public function modifyRelationOptions($options, $source, $args, Field $field, $isCount)
    {
        $model = $field->getModel();

        $search = isset($args['search']) ? trim($args['search']) : null;
        $searchFields = $this->_getModelSearchFields($model);

        if($search === null || $search == '' || count($searchFields) == 0) {
            return;
        }

        $conditions = isset($options['conditions']) && !empty($options['conditions']) ? $options['conditions'] . ' AND ' : '';
        $bind = isset($options['bind']) && !empty($options['bind']) ? $options['bind'] : [];

        $searchConditions = [];

        foreach($searchFields as $searchField) {
            $searchConditions[] = '[' . $searchField . '] LIKE CONCAT(:searchQuery:, "%")';
        }

        $bind['searchQuery'] = $search;
        $conditions .= '(' . implode(' AND ', $searchConditions) . ')';

        $options['conditions'] = $conditions;
        $options['bind'] = $bind;

        return $options;
    }

    protected function _modifyAllQueryForSearchField(QueryBuilder $query, $searchField, $modelShort){


    }

    protected function _getOrClause($searchField, $value, $modelShort){

        return '[' . $modelShort . '].[' . $searchField . '] LIKE ' . $value;
    }

    protected function _getModelSearchFields($model){

        $modelInstance = new $model();
        $fields = [];

        if(method_exists($modelInstance, 'searchFields')) {

            $fields = array_merge($fields, $modelInstance->searchFields());
        }

        return $fields;
    }
}