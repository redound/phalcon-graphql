<?php

namespace PhalconGraphQL\Plugins;

use Phalcon\Di\DiInterface;
use PhalconGraphQL\Definition\UnionType;

interface UnionTypePluginInterface
{
    public function beforeBuildUnionType(UnionType $unionType, DiInterface $di);
    public function afterBuildUnionType(UnionType $unionType, DiInterface $di);
}