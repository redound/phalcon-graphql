<?php

namespace PhalconGraphQL;

use GraphQL\GraphQL;
use Phalcon\Http\Request;
use PhalconGraphQL\Constants\Services;
use PhalconGraphQL\Definition\Field;
use PhalconGraphQL\Definition\ObjectType;
use PhalconGraphQL\Definition\Schema;
use PhalconGraphQL\GraphQL\SchemaFactory;
use PhalconGraphQL\Handlers\Handler;

class Dispatcher extends \PhalconGraphQL\Mvc\Plugin
{
    protected $defaultNamespace;

    public function setDefaultNamespace($namespace)
    {
        $this->defaultNamespace = rtrim($namespace, '\\');
    }

    public function createHandler(ObjectType $objectType, Schema $schema)
    {
        $handler = null;
        $handlerClassName = $objectType->getHandler();

        if ($handlerClassName) {

            $handler = new $handlerClassName;

        } else {

            $handlerClassName = $this->defaultNamespace . '\\' . $objectType->getName() . 'Handler';

            if (!class_exists($handlerClassName)) {
                $handlerClassName = Handler::class;
            }

            $handler = new $handlerClassName;
        }

        if ($handler instanceof \Phalcon\Di\Injectable) {
            $handler->setDI($this->di);
        }

        if($handler instanceof Handler){

            $handler->setSchema($schema);
            $handler->setObjectType($objectType);
        }

        return $handler;
    }

    public function createResolver($handler, Field $field)
    {
        return function ($source, $args) use ($handler, $field) {

            $resolvers = $field->getResolvers();
            $fieldName = $field->getName();

            if (empty($resolvers)) {
                return $handler->$fieldName($source, $args, $field);
            }

            foreach ($resolvers as $resolverFn) {

                if (is_callable($resolverFn)) {

                    $source = call_user_func($resolverFn, $source, $args, $field);
                }
                else if (is_string($resolverFn)) {

                    $parts = explode('::', $resolverFn);

                    if (count($parts) === 2) {

                        $className = $parts[0];
                        $methodName = $parts[1];

                        $obj = new $className;
                        if ($obj instanceof \Phalcon\Di\Injectable) {
                            $obj->setDI($this->di);
                        }

                        $source = $obj->$methodName($source, $args, $field);
                    }
                    else if(class_exists($resolverFn, true) && method_exists($resolverFn, 'resolve')) {

                        $resolverObject = new $resolverFn();
                        if ($resolverObject instanceof \Phalcon\Di\Injectable) {
                            $resolverObject->setDI($this->di);
                        }

                        $source = $resolverObject->resolve($source, $args, $field);
                    }
                    else {

                        $source = $handler->$resolverFn($source, $args, $field);
                    }
                }
            }

            return $source;
        };
    }

    public function dispatch(Schema $schema, Request $request = null)
    {
        $graphqlSchema = SchemaFactory::build($this, $schema, $this->getDI());

        if(!$request) {
            $request = $this->di->get(Services::REQUEST);
        }

        $data = $request->getPostedData();

        $requestString = isset($data['query']) && !empty($data['query']) ? $data['query'] : null;
        $operationName = isset($data['operation']) && !empty($data['operation']) ? $data['operation'] : null;
        $variableValues = isset($data['variables']) && !empty($data['variables']) ? $data['variables'] : null;
        
        $result = GraphQL::execute(
            $graphqlSchema,
            $requestString,
            null, // rootValue
            null, // context
            $variableValues,
            $operationName
        );

        return $result;
    }
}
