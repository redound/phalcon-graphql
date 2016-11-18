<?php

namespace PhalconGraphQL\Di;

use PhalconGraphQL\Constants\Services;
use PhalconGraphQL\Dispatcher;
use PhalconGraphQL\Http\Response;

class FactoryDefault extends \PhalconApi\Di\FactoryDefault
{
    public function __construct()
    {
        parent::__construct();

        $this->setShared(Services::RESPONSE, new Response());

        $this->setShared(Services::GRAPHQL_DISPATCHER, new Dispatcher());
    }
}