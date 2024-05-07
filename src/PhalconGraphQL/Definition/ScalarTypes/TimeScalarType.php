<?php

namespace PhalconGraphQL\Definition\ScalarTypes;

use GraphQL\Language\AST\IntValueNode;
use GraphQL\Language\AST\StringValueNode;
use GraphQL\Type\Definition\ScalarType;

class TimeScalarType extends ScalarType
{
    const DEFAULT_TIME_FORMAT = 'H:i:s';

    public string $name = 'Time';
    public ?string $description = 'The `Time` scalar type represents a time';

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

    public function parseLiteral($valueNode, array $variables = null)
    {
        if ($valueNode instanceof StringValueNode || $valueNode instanceof IntValueNode) {
            return $this->parseValue($valueNode->value);
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