<?php

trait FindModelTrait
{
    protected function _find(\PhalconGraphQL\Definition\Fields\Field $field, $args)
    {
        $id = $args['id'];
        $model = $this->getModel($field);

        $result = $model::findFirst($id);

        return $this->_getFindResponse($result, $field);
    }

    protected function _getFindResponse($result, \PhalconGraphQL\Definition\Fields\Field $field)
    {
        return $result;
    }
}