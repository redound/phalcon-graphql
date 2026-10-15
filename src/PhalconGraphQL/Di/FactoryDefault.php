<?php

namespace PhalconGraphQL\Di;

use PhalconApi\Auth\TokenParserInterface;
use PhalconGraphQL\Constants\Services;
use PhalconGraphQL\Dispatcher;
use PhalconGraphQL\Http\Response;

class FactoryDefault extends \PhalconApi\Di\FactoryDefault
{
    public function __construct(?TokenParserInterface $tokenParser = null)
    {
        parent::__construct($tokenParser);

        $this->setShared(Services::RESPONSE, new Response());

        $this->setShared(Services::GRAPHQL_DISPATCHER, new Dispatcher());
    }
}
