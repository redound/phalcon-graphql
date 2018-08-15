<?php

use PhalconGraphQL\Definition\Fields\Field;
use PhalconApi\Constants\ErrorCodes;
use PhalconGraphQL\Exception;
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Query\BuilderInterface as QueryBuilder;

trait ModelQueryTrait
{
    protected function _onNotAllowed(array $args, Field $field)
    {
        throw new Exception(ErrorCodes::ACCESS_DENIED, 'Operation is not allowed');
    }

    protected function _modifyQuery(QueryBuilder $query, array $args, Field $field, $isCount)
    {
    }

    protected function _getModelPrimaryKey(Field $field)
    {
        $modelClass = $this->getModel($field);

        /** @var Model $modelInstance */
        $modelInstance = new $modelClass();

        return $modelInstance->getModelsMetaData()->getIdentityField($modelInstance) ?: 'id';
    }

    protected function _beforeHandle(array $args, Field $field)
    {
    }

    protected function _afterHandle(array $args, Field $field)
    {
    }

    protected function _invokePlugins(Field $field, $methodName, array $arguments=[], $input=null)
    {
        $plugins = $this->_getPlugins($field);
        $response = $input !== null ? $input : [];

        foreach($plugins as $plugin){

            if($plugin instanceof \PhalconGraphQL\Plugins\ModelHandlerPluginInterface){

                $args = $arguments;
                if($input !== null){
                    array_unshift($args, $response);
                }

                $responseItem = call_user_func_array([$plugin, $methodName], $args);

                if ($input !== null && $responseItem && is_array($responseItem)) {
                    $response = $responseItem;
                }
                else if($input === null){
                    $response[] = $responseItem;
                }
            }
        }

        return $response;
    }

    /**
     * @param Field $field
     *
     * @return \PhalconGraphQL\Plugins\PluginInterface[]
     */
    protected function _getPlugins(Field $field)
    {
        return array_merge($this->schema->getPlugins(), $field->getPlugins());
    }
}