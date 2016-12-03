<?php

namespace PhalconGraphQL\Plugins;

use Phalcon\DiInterface;
use PhalconGraphQL\Definition\Fields\Field;

interface FieldPluginInterface
{
    public function beforeBuildField(Field $field, DiInterface $di);
    public function afterBuildField(Field $field, DiInterface $di);
}