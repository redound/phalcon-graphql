<?php

namespace PhalconGraphQL\Definition\ScalarTypes;

use GraphQL\Language\AST\IntValue;
use GraphQL\Language\AST\StringValue;
use GraphQL\Type\Definition\ScalarType;

class TimeScalarType extends ScalarType
{
    const DEFAULT_TIME_FORMAT = 'H:i:s';

    public $name = 'Time';
    public $description = 'The `Time` scalar type represents a time';

    protected $fallbackFormats = ["H:i"];

    protected $format;

    public function __construct($format = self::DEFAULT_TIME_FORMAT)
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

            $date = \DateTime::createFromFormat($this->format, $value);

            if($date === false){

                foreach($this->fallbackFormats as $format){

                    $date = \DateTime::createFromFormat($format, $value);

                    if($date !== false){
                        break;
                    }
                }
            }
        }
        catch(\Exception $e){}

        if($date === false){
            throw new \Exception('Invalid time: ' . $value);
        }

        return $date;
    }
}