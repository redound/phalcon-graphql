<?php

namespace PhalconGraphQL\Definition\ScalarTypes;

use GraphQL\Language\AST\IntValueNode;
use GraphQL\Language\AST\StringValueNode;
use GraphQL\Type\Definition\ScalarType;

class DateScalarType extends ScalarType
{
    const DEFAULT_DATE_FORMAT = 'Y-m-d';

    public string $name = 'Date';
    public ?string $description = 'The `Date` scalar type represents a date';

    protected $fallbackFormats = ["Y-m-d H:i:s", "Y-m-d H:i", "Y-m-d", \DateTime::ISO8601, 'Y-m-d\TH:i:s.uP'];

    protected string $externalFormat;
    protected string $internalFormat;

    public function __construct($externalFormat=self::DEFAULT_DATE_FORMAT, $internalFormat=self::DEFAULT_DATE_FORMAT)
    {
        parent::__construct();

        $this->externalFormat = $externalFormat;
        $this->internalFormat = $internalFormat;
    }

    public function serialize($value)
    {
        if ($value === null) {
            return null;
        }

        $date = $this->getDateTime($value);

        return $date->format($this->externalFormat);
    }

    public function parseValue($value)
    {
        if ($value === null) {
            return null;
        }

        $date = $this->getDateTime($value);

        return $date->format($this->internalFormat);
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

            if(is_numeric($value)){
                $date = new \DateTime('@' . $value);
            }
            else {
                $date = \DateTime::createFromFormat($this->externalFormat, $value);
            }

            if($date === false){

                foreach($this->fallbackFormats as $format){

                    $date = \DateTime::createFromFormat($format, $value);

                    if($date !== false){
                        break;
                    }
                }
            }

            if($date){
                $date->setTimezone(new \DateTimeZone('UTC'));
            }
        }
        catch(\Exception $e){}

        if($date === false){
            throw new \Exception('Invalid date: ' . $value);
        }

        return $date;
    }
}