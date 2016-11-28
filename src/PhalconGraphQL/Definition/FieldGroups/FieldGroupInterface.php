<?php

namespace PhalconGraphQL\Definition\FieldGroups;

use Phalcon\DiInterface;
use PhalconGraphQL\Definition\Field;
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

    public function build(Schema $schema, DiInterface $di);
}