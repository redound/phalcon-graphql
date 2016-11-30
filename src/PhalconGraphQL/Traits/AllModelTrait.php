<?php

trait AllModelTrait
{
    protected function _all($args, \PhalconGraphQL\Definition\Fields\Field $field, \PhalconGraphQL\Definition\Schema $schema)
    {
        $model = $this->getModel($field);

        $options = [];

        if(isset($args['offset']) && !empty($args['offset'])){
            $options['offset'] = (int)$args['offset'];
        }

        if(isset($args['limit']) && !empty($args['limit'])){
            $options['limit'] = (int)$args['limit'];
        }

        $result = $model::find($options);

        return $this->_getAllResponse($result, $field, $schema);
    }

    protected function _getAllResponse($result, \PhalconGraphQL\Definition\Fields\Field $field, \PhalconGraphQL\Definition\Schema $schema)
    {
        $model = $this->getModel($field);
        $returnType = $schema->findObjectType($field->getType());

        if($returnType->getHandler() == \PhalconGraphQL\Handlers\ListEmbedHandler::class) {

            return \PhalconGraphQL\Responses\ListEmbedResponse::factory($result, $model::count());
        }
        else {

            return $result;
        }
    }
}