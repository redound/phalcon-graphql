<?php

namespace PhalconGraphQL\Definition;

use Phalcon\Db\Column;

class Types
{
    const STRING = "String";
    const INT = "Int";
    const FLOAT = "Float";
    const BOOLEAN = "Boolean";
    const ID = "ID";
    const DATE = "Date";
    const DATE_TIME = "DateTime";
    const JSON = "JSON";

    const QUERY = "Query";
    const MUTATION = "Mutation";

    const VIEWER = "Viewer";

    public static function scalars(){

        return [self::STRING, self::INT, self::FLOAT, self::BOOLEAN, self::ID, self::DATE, self::DATE_TIME, self::JSON];
    }

    public static function addConnection($name){

        return $name . 'Connection';
    }

    public static function addEdge($name){

        return $name . 'Edge';
    }

    public static function addList($name){

        return $name . 'List';
    }

    public static function addInput($name){

        return ucfirst($name) . 'Input';
    }

    public static function addCreateInput($name){

        return 'Create' . ucfirst($name) . 'Input';
    }

    public static function addUpdateInput($name){

        return 'Update' . ucfirst($name) . 'Input';
    }

    public static function getMappedDatabaseType($type)
    {
        $responseType = null;

        switch ($type) {

            case Column::TYPE_INTEGER:
            case Column::TYPE_BIGINTEGER: {

                $responseType = Types::INT;
                break;
            }

            case Column::TYPE_DECIMAL:
            case Column::TYPE_DOUBLE:
            case Column::TYPE_FLOAT: {

                $responseType = Types::FLOAT;
                break;
            }

            case Column::TYPE_BOOLEAN: {

                $responseType = Types::BOOLEAN;
                break;
            }

            case Column::TYPE_VARCHAR:
            case Column::TYPE_CHAR:
            case Column::TYPE_TEXT:
            case Column::TYPE_BLOB:
            case Column::TYPE_MEDIUMBLOB:
            case Column::TYPE_LONGBLOB: {

                $responseType = Types::STRING;
                break;
            }

            case Column::TYPE_DATE: {

                $responseType = Types::DATE;
                break;
            }

            case Column::TYPE_TIMESTAMP:
            case Column::TYPE_DATETIME: {

                $responseType = Types::DATE_TIME;
                break;
            }

            case Column::TYPE_JSON: {

                $responseType = Types::JSON;
                break;
            }

            default:
                $responseType = Types::STRING;
        }

        return $responseType;
    }
}