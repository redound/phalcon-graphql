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
}