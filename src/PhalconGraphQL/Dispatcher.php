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
use PhalconGraphQL\Plugins\PluginInterface;
use PhalconGraphQL\Resolvers\Resolver;

class Dispatcher extends \PhalconGraphQL\Mvc\Plugin
{
    protected $defaultNamespace;

    protected $handlerCache = [];
    protected $resolverCache = [];

    public function setDefaultNamespace($namespace)
    {
        $this->defaultNamespace = rtrim($namespace, '\\');
    }

    public function getHandler(Schema $schema, ObjectType $objectType, Field $field)
    {
        $handlerClassName = $field->getHandler() ? $field->getHandler() : $objectType->getHandler();
        $objectKey = $objectType->getName() . ':' . $handlerClassName;

        if(array_key_exists($objectKey, $this->handlerCache)){
            return $this->handlerCache[$objectKey];
        }

        $handler = null;

        if ($handlerClassName) {

            $handler = new $handlerClassName;

        } else {

            $handlerClassName = $this->defaultNamespace . '\\' . $objectType->getName() . 'Handler';

            if (class_exists($handlerClassName)) {
                $handler = new $handlerClassName;
            }
        }

        if($handler === null){
            return null;
        }

        if ($handler instanceof \Phalcon\Di\Injectable) {
            $handler->setDI($this->di);
        }

        if($handler instanceof Handler){

            $handler->setSchema($schema);
            $handler->setObjectType($objectType);
        }

        $this->handlerCache[$objectKey] = $handler;

        return $handler;
    }

    public function createResolver(Schema $schema, ObjectType $objectType, Field $field)
    {
        $dispatcher = $this;

        return function ($source, $args) use ($schema, $objectType, $field, $dispatcher) {

            /** @var PluginInterface $plugin */
            foreach($schema->getPlugins() as $plugin){
                $plugin->beforeResolve($schema, $objectType, $field);
            }

            $resolver = $field->getResolver();
            $fieldName = $field->getName();
            $handler = $dispatcher->getHandler($schema, $objectType, $field);

            if ($handler) {
                return $handler->$fieldName($source, $args, $field);
            }

            if (is_callable($resolver)) {

                $source = call_user_func($resolver, $source, $args, $field);
            }
            else if (is_string($resolver)) {

                $parts = explode('::', $resolver);

                if (count($parts) === 2) {

                    $className = $parts[0];
                    $methodName = $parts[1];

                    $obj = null;

                    if(array_key_exists($resolver, $this->resolverCache)){

                        $obj = $this->resolverCache[$resolver];
                    }
                    else {

                        $obj = new $className;
                        if ($obj instanceof \Phalcon\Di\Injectable) {
                            $obj->setDI($this->di);
                        }

                        $this->resolverCache[$resolver] = $obj;
                    }

                    $source = $obj->$methodName($source, $args, $field);
                }
                else if(class_exists($resolver, true) && method_exists($resolver, 'resolve')) {

                    $resolverObject = null;

                    if(array_key_exists($resolver, $this->resolverCache)){

                        $resolverObject = $this->resolverCache[$resolver];
                    }
                    else {

                        $resolverObject = new $resolver();

                        if ($resolverObject instanceof \Phalcon\Di\Injectable) {
                            $resolverObject->setDI($this->di);
                        }

                        $this->resolverCache[$resolver] = $resolverObject;
                    }

                    if($resolverObject instanceof Resolver){

                        $resolverObject->setSchema($schema);
                        $resolverObject->setObjectType($objectType);
                    }

                    $source = $resolverObject->resolve($source, $args, $field);
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
