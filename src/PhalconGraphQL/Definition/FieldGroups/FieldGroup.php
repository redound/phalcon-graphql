<?php

namespace PhalconGraphQL\Definition\FieldGroups;

use Phalcon\DiInterface;
use PhalconGraphQL\Definition\Field;
use PhalconGraphQL\Definition\Schema;

class FieldGroup implements FieldGroupInterface
{
    protected $_fields = [];

    public function add(Field $field){

        $this->_fields[] = $field;
        return $this;
    }

    public function build(Schema $schema, DiInterface $di)
    {
        // Empty
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