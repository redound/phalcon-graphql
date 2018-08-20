<?php

namespace PhalconGraphQL\Responses;

class ListEmbedResponse
{
    protected $_items;
    protected $_totalCount;
    protected $_offset;
    protected $_limit;

    public function __construct($items=[], $totalCount=null, $offset=null, $limit=null)
    {
        $this->_items = $items;
        $this->_totalCount = $totalCount;
        $this->_offset = $offset;
        $this->_limit = $limit;
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

    public function getOffset()
    {
        return $this->_offset;
    }

    public function getLimit()
    {
        return $this->_limit;
    }

    public function setOffset($offset)
    {
        $this->_offset = $offset;
    }

    public function setLimit($limit)
    {
        $this->_limit = $limit;
    }


    public static function factory($items=[], $totalCount=null, $offset=null, $limit=null)
    {
        return new static($items, $totalCount, $offset, $limit);
    }
}