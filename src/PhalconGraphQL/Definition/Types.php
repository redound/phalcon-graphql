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

    const QUERY = "Query";
    const MUTATION = "Mutation";

    const VIEWER = "Viewer";

    public static function connection($name){

        return $name . 'Connection';
    }

    public static function edge($name){

        return $name . 'Edge';
    }

    public static function input($name){

        return ucfirst($name) . 'Input';
    }

    public static function createInput($name){

        return 'Create' . ucfirst($name) . 'Input';
    }

    public static function updateInput($name){

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

            // TODO: Implement?
//            case Column::TYPE_DATE:
//            case Column::TYPE_DATETIME: {
//
//                $responseType = self::TYPE_DATE;
//                break;
//            }

            default:
                $responseType = Types::STRING;
        }

        return $responseType;
    }
}