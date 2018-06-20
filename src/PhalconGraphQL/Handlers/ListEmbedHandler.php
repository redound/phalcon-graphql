<?php

namespace PhalconGraphQL\Handlers;

use PhalconGraphQL\Responses\ListEmbedResponse;

class ListEmbedHandler
{
    /**
     * @param ListEmbedResponse $source
     *
     * @return mixed
     */
    public function items($source)
    {
        return $source instanceof ListEmbedResponse ? $source->getItems() : $source;
    }

    /**
     * @param ListEmbedResponse $source
     *
     * @return int
     */
    public function totalCount($source)
    {
        return $source instanceof ListEmbedResponse ? $source->getTotalCount() : null;
    }

    /**
     * @param ListEmbedResponse $source
     *
     * @return int
     */
    public function itemCount($source)
    {
        return $source instanceof ListEmbedResponse ? count($source->getItems()) : count($source);
    }

    /**
     * @param ListEmbedResponse $source
     *
     * @return int
     */
    public function offset($source)
    {
        return $source instanceof ListEmbedResponse ? $source->getOffset() : null;
    }

    /**
     * @param ListEmbedResponse $source
     *
     * @return int
     */
    public function limit($source)
    {
        return $source instanceof ListEmbedResponse ? $source->getLimit() : null;
    }

    public function __call($name, $arguments)
    {
        list($source, $args, $field) = $arguments;

        $functionName = 'get' . ucfirst($name);

        if(method_exists($source, $functionName)){
            return $source->$functionName($source);
        }
        else {

            throw new \Exception('Field ' . $name . ' not supported for list embed handler');
        }
    }
}