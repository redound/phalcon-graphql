<?php

trait AllModelTrait
{
    protected function _all(\PhalconGraphQL\Definition\Fields\Field $field)
    {
        $model = $this->getModel($field);

        return $model::find();
    }
}