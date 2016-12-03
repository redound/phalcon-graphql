<?php

namespace PhalconGraphQL\Definition\FieldGroups;

use Phalcon\DiInterface;
use PhalconGraphQL\Definition\Fields\Field;
use PhalconGraphQL\Definition\Schema;

class FieldGroup implements FieldGroupInterface
{
    protected $_fields = [];
    protected $_handler;
    protected $_build = false;

    public function __construct($handler=null)
    {
        if($handler === null){
            $handler = $this->getDefaultHandler();
        }

        $this->_handler = $handler;
    }

    public function add(Field $field){

        $this->_fields[] = $field;
        return $this;
    }

    /**
     * @param string $handler Handler for the ObjectType
     *
     * @return static
     */
    public function handler($handler)
    {
        $this->_handler = $handler;
        return $this;
    }

    public function getHandler()
    {
        return $this->_handler;
    }

    public function build(Schema $schema, DiInterface $di)
    {
        $fields = array_merge($this->_fields, $this->getDefaultFields($schema, $di));

        /** @var Field $field */
        foreach($fields as $field){

            if(!$field->getHandler()) {
                $field->handler($this->_handler);
            }
        }

        $this->_fields = $fields;
        $this->_build = true;
    }

    protected function getDefaultFields(Schema $schema, DiInterface $di){

        // Override
        return [];
    }

    protected function getDefaultHandler(){

        return null;
    }

    /**
     * @return Field[]
     */
    public function getFields()
    {
        return $this->_fields;
    }

    /**
     * @return static
     */
    public static function factory($handler=null){

        return new FieldGroup($handler);
    }
}