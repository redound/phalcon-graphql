<?php

namespace PhalconGraphQL\Definition\ScalarTypes;

use GraphQL\Language\AST\IntValue;
use GraphQL\Language\AST\StringValue;
use GraphQL\Type\Definition\ScalarType;

class DateTimeScalarType extends DateScalarType
{
    const DEFAULT_DATE_FORMAT = 'Y-m-d\TH:i:s\Z';

    public $name = 'DateTime';
    public $description = 'The `DateTime` scalar type represents a date/time in ISO format';

    public function __construct($format = self::DEFAULT_DATE_FORMAT)
    {
        parent::__construct($format);
    }
}