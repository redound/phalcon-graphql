<?php

namespace PhalconGraphQL\Definition\Fields;

use Phalcon\DiInterface;
use PhalconGraphQL\Core;
use PhalconGraphQL\Definition\InputField;
use PhalconGraphQL\Definition\Schema;
use PhalconGraphQL\Resolvers\AllModelResolver;

class AllModelField extends ModelField
{
    protected $_pagingMode = null;

    public function __construct($model=null, $name=null, $type=null, $description=null, $embedMode=null)
    {
        if($name === null){
            $name = 'all' . ucfirst(Core::getShortClass($model)) . 's';
        }

        parent::__construct($model, $name, $type, $description, $embedMode);

        $this
            ->resolver(AllModelResolver::class)
            ->isList()
            ->nonNull();
    }

    public function pagingMode($pagingMode)
    {
        $this->_pagingMode = $pagingMode;
        return $this;
    }

    public function pagingOffset()
    {
        $this->_pagingMode = Schema::PAGING_MODE_OFFSET;
        return $this;
    }

    public function noPaging()
    {
        $this->_pagingMode = Schema::PAGING_MODE_NONE;
        return $this;
    }

    public function getPagingMode()
    {
        return $this->_pagingMode;
    }

    public function build(Schema $schema, DiInterface $di)
    {
        if ($this->_built) {
            return;
        }

        $pagingMode = $this->_pagingMode;
        if($pagingMode === null){
            $pagingMode = $schema->getPagingMode();
        }

        if($pagingMode == Schema::PAGING_MODE_OFFSET){

            $this
                ->arg(InputField::int('offset'))
                ->arg(InputField::int('limit'));
        }

        parent::build($schema, $di);
    }

    public static function factory($model=null, $name=null, $type=null, $description=null)
    {
        return new AllModelField($model, $name, $type, $description);
    }
}