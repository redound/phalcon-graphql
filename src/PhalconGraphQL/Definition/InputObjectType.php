<?php

namespace PhalconGraphQL\Definition;

use Phalcon\Di\DiInterface;
use PhalconGraphQL\Exception;

class InputObjectType
{
    protected $_name;
    protected $_description;
    protected $_handler;
    protected $_fields = [];

    protected $_built = false;

    public function __construct($name=null)
    {
        $this->_name = $name;
    }

    public function name($name)
    {
        $this->_name = $name;
    }

    public function getName()
    {
        return $this->_name;
    }

    public function description($description)
    {
        $this->_description = $description;
    }

    public function getDescription()
    {
        return $this->_description;
    }

    /**
     * @param InputField $field Add field to InputbjectType
     *
     * @return static
     */
    public function field(InputField $field)
    {
        // Remove field if already exists
        $this->removeField($field->getName());

        $this->_fields[] = $field;
        $this->_built = false;

        return $this;
    }

    public function removeField($fieldName)
    {
        $foundIndex = null;

        foreach($this->_fields as $index => $field){

            if($field->getName() == $fieldName){

                $foundIndex = $index;
                break;
            }
        }

        if($foundIndex !== null) {
            array_splice($this->_fields, $foundIndex, 1);
        }

        return $this;
    }

    public function fieldExists($fieldName)
    {
        foreach($this->_fields as $index => $field){

            if($field->getName() == $fieldName){

                return true;
            }
        }

        return false;
    }

    public function getFields()
    {
        if(!$this->_built){
            throw new Exception("Unable to get fields from '" . $this->getName() . "', input object type is not built yet'");
        }

        return $this->_fields;
    }

    public function build(Schema $schema, DiInterface $di){

        /** @var InputField $field */
        foreach($this->_fields as $field){
            $field->build($schema, $di);
        }

        $this->_built = true;
    }

    public static function factory($name)
    {
        return new static($name);
    }
}
