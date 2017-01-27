<?php

namespace PhalconGraphQL\Definition\FieldGroups;

use Phalcon\DiInterface;
use PhalconGraphQL\Definition\Fields\Field;
use PhalconGraphQL\Definition\Schema;

interface FieldGroupInterface
{
    /**
     * @return Field[]
     */
    public function getFields();

    /**
     * @return mixed
     */
    public function getHandler();

    public function allow($roles);

    public function deny($roles);

    public function allowField($fieldName, $roles);

    public function denyField($fieldName, $roles);

    public function plugin($plugin);

    public function build(Schema $schema, DiInterface $di);
}