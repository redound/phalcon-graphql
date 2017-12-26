<?php

namespace PhalconGraphQL\Definition\ScalarTypes;

use GraphQL\Language\AST\IntValue;
use GraphQL\Language\AST\StringValue;
use GraphQL\Type\Definition\ScalarType;

class DateScalarType extends ScalarType
{
    const DEFAULT_DATE_FORMAT = 'Y-m-d';

    public $name = 'Date';
    public $description = 'The `Date` scalar type represents a date';

    protected $format;

    public function __construct($format = self::DEFAULT_DATE_FORMAT)
    {
        parent::__construct();

        $this->format = $format;
    }

    public function serialize($value)
    {
        if ($value === null) {
            return null;
        }

        $date = $this->getDateTime($value);

        return $date->format($this->format);
    }

    public function parseValue($value)
    {
        if ($value === null) {
            return null;
        }

        $date = $this->getDateTime($value);

        return $date->format($this->format);
    }

    public function parseLiteral($ast)
    {
        if ($ast instanceof \StringValue || $ast instanceof \IntValue) {
            return $this->parseValue($ast->value);
        }

        return null;
    }

    protected function getDateTime($value)
    {
        $date = false;

        try {
            $date = new \DateTime(is_numeric($value) ? '@' . $value : $value);
        }
        catch(\Exception $e){}

        if($date === false){
            throw new \Exception('Invalid date: ' . $value);
        }

        return $date;
    }
}