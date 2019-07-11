<?php

/**
 * join
 * contains
 * get
 * first
 * insert
 * last
 * map
 * merge
 * pop
 * push
 * remove
 * reverse
 * set
 * toArray
 * hasKey
 * put
 * putAll
 * sort
 * add
 * diff
 * count
 * filter
 * intersect
 * jsonSerialize
 *
 */

namespace Xav;


class Collection implements Access
{
    use InteractInput,
        Macroable;

    public $items = array();

    public function __construct($items)
    {
        $this->items = $items;

        //$this->propSerializer();
    }


    public function insert($value)
    {
        $this->items = $value;
    }

    public function push($value)
    {
        $this->insert($value);


    }

    public function add()
    {

    }


    public function input()
    {
        return $this->items ?? [];
    }


}