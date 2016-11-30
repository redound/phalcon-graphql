<?php

namespace PhalconGraphQL;

use GraphQL\GraphQL;
use Phalcon\Http\Request;
use PhalconGraphQL\Constants\Services;
use PhalconGraphQL\Definition\Fields\Field;
use PhalconGraphQL\Definition\ObjectType;
use PhalconGraphQL\Definition\Schema;
use PhalconGraphQL\GraphQL\SchemaFactory;
use PhalconGraphQL\Handlers\Handler;

class Dispatcher extends \PhalconGraphQL\Mvc\Plugin
{
    protected $defaultNamespace;

    protected $handlerCache = [];

    public function setDefaultNamespace($namespace)
    {
        $this->defaultNamespace = rtrim($namespace, '\\');
    }

    public function getHandler(Schema $schema, ObjectType $objectType, $fieldGroup)
    {
        $handlerClassName = $fieldGroup && $fieldGroup->getHandler() ? $fieldGroup->getHandler() : $objectType->getHandler();
        $objectKey = $objectType->getName() . ':' . $handlerClassName;

        if(array_key_exists($objectKey, $this->handlerCache)){
            return $this->handlerCache[$objectKey];
        }

        $handler = null;

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
            $handler->setFieldGroup($fieldGroup);
        }

        $this->handlerCache[$objectKey] = $handler;

        return $handler;
    }

    public function createResolver($schema, ObjectType $objectType, Field $field, $fieldGroup)
    {
        $dispatcher = $this;

        return function ($source, $args) use ($schema, $objectType, $field, $dispatcher, $fieldGroup) {

            $resolvers = $field->getResolvers();
            $fieldName = $field->getName();
            $handler = $dispatcher->getHandler($schema, $objectType, $fieldGroup);

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
        $variableValues = isset($data['variables']) && !empty($data['variables']) ? json_decode($data['variables'], true) : null;

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
