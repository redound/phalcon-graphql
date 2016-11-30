<?php

trait FindModelTrait
{
    protected function _find(\PhalconGraphQL\Definition\Fields\Field $field, $id)
    {
        $model = $this->getModel($field);

        return $model::findFirst($id);
    }
}