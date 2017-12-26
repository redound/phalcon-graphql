<?php

namespace PhalconGraphQL;

class Exception extends \PhalconApi\Exception implements \GraphQL\Error\ClientAware
{
    public function isClientSafe()
    {
        return true;
    }

    public function getCategory()
    {
        return 'application';
    }
}