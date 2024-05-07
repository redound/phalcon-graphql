<?php

namespace PhalconGraphQL\Definition\ScalarTypes;

use GraphQL\Type\Definition\ScalarType;

class DateTimeScalarType extends DateScalarType
{
    const DEFAULT_EXTERNAL_FORMAT = 'Y-m-d\TH:i:s\Z';
    const DEFAULT_INTERNAL_FORMAT = 'Y-m-d H:i:s';

    public string $name = 'DateTime';
    public ?string $description = 'The `DateTime` scalar type represents a date/time in ISO format';

    public function __construct($externalFormat=self::DEFAULT_EXTERNAL_FORMAT, $internalFormat=self::DEFAULT_INTERNAL_FORMAT)
    {
        parent::__construct($externalFormat, $internalFormat);
    }
}