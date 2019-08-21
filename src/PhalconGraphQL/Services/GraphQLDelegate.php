<?php

namespace PhalconGraphQL\Services;

use PhalconApi\Constants\ErrorCodes;
use PhalconGraphQL\Constants\Services;
use PhalconGraphQL\Dispatcher;
use PhalconGraphQL\Exception;

class GraphQLDelegate extends \PhalconGraphQL\Mvc\Plugin
{
    protected $_queriesPath;
    protected $_mutationsPath;

    protected $_debug;

    protected $_graphqlSchema;
    protected $_schema;

    /** @var Dispatcher */
    protected $_dispatcher;

    public function __construct($queriesPath, $mutationsPath, $debug=false)
    {
        $this->_queriesPath = $queriesPath;
        $this->_mutationsPath = $mutationsPath;

        $this->_debug = $debug;

        $this->_dispatcher = $this->di->get(Services::GRAPHQL_DISPATCHER);
    }

    public function setGraphqlSchema($graphqlSchema)
    {
        $this->_graphqlSchema = $graphqlSchema;
    }

    public function setSchema($schema)
    {
        $this->_schema = $schema;
    }

    public function query($name, $resultPath=null, $variables=null){

        $content = $this->_getContent($this->_queriesPath, $name);

        return $this->_dispatch($content, $variables, $resultPath);
    }

    public function mutation($name, $resultPath=null, $variables=null){

        $content = $this->_getContent($this->_mutationsPath, $name);

        return $this->_dispatch($content, $variables, $resultPath);
    }


    protected function _dispatch($content, $variables, $resultPath=null){

        $data = [
            'query' => $content,
            'variables' => $variables
        ];

        $result = $this->_dispatcher->dispatch($this->_schema, $this->_graphqlSchema, $this->_debug, $data);
        if(!$result || isset($result['errors'])){

            $errors = isset($result['errors']) ? $result['errors'] : [];
            $firstError = count($errors) > 0 ? $errors[0] : null;
            $firstMessage = $firstError && isset($firstError['message']) ? $firstError['message'] : 'Unknown error';
            $firstCode = $firstError && isset($firstError['code']) ? $firstError['code'] : ErrorCodes::DATA_FAILED;

            throw new Exception($firstCode, $firstMessage, $firstError);
        }

        $basePath = 'data';
        $fullResultPath = $resultPath ? $basePath . '.' . $resultPath : $basePath;

        return $this->_getValue($result, $fullResultPath);
    }

    protected function _getContent($base, $name){

        $path = $base . '/' . $name . '.gql';
        $content = file_get_contents($path);

        // Resolve includes
        $content = preg_replace_callback('/#import\s?"(.+)"/', function($match) use ($base) {

            $includePath = $base . '/' . $match[1];
            return file_get_contents($includePath);

        }, $content);

        return $content;
    }

    protected function _getValue($data, $path) {

        if(!$path){
            return $data;
        }

        $temp = $data;

        foreach(explode(".", $path) as $ndx) {
            $temp = isset($temp[$ndx]) ? $temp[$ndx] : null;
        }

        return $temp;
    }
}