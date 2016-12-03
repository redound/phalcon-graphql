<?php

namespace PhalconGraphQL\Plugins\Paging;

use Phalcon\DiInterface;
use PhalconGraphQL\Definition\Fields\AllModelField;
use PhalconGraphQL\Definition\Fields\Field;
use PhalconGraphQL\Definition\InputField;
use PhalconGraphQL\Plugins\Plugin;

class OffsetLimitPagingPlugin extends Plugin
{
    public function beforeBuildField(Field $field, DiInterface $di)
    {
        if($field instanceof AllModelField) {

            $field
                ->arg(InputField::int('offset'))
                ->arg(InputField::int('limit'));
        }
    }
}