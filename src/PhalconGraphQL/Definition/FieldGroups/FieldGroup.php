<?php

namespace PhalconGraphQL\Definition\FieldGroups;

use Phalcon\DiInterface;
use PhalconGraphQL\Definition\Field;
use PhalconGraphQL\Definition\Schema;

class FieldGroup implements FieldGroupInterface
{
    protected $_fields = [];
    protected $_handler;
    protected $_build = false;

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
            $field->build($schema, $di);
        }

        $this->_fields = $fields;
        $this->_build = true;
    }

    protected function getDefaultFields(Schema $schema, DiInterface $di){

        // Override
        return [];
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
    public static function factory(){

        return new FieldGroup();
    }
}