<?php

namespace PhalconGraphQL\Responses;

class ListEmbedResponse
{
    protected $_items;

    protected $_totalCount;

    public function __construct($items=[], $totalCount=null)
    {
        $this->_items = $items;
        $this->_totalCount = $totalCount;
    }

    public function items($items)
    {
        $this->_items = $items;
    }

    public function getItems()
    {
        return $this->_items;
    }

    public function totalCount($totalCount)
    {
        $this->_totalCount = $totalCount;
    }

    public function getTotalCount()
    {
        return $this->_totalCount;
    }


    public static function factory($items=[], $totalCount=null)
    {
        return new ListEmbedResponse($items, $totalCount);
    }
}