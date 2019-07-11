<?php


namespace Xav;


class Parameters
{
    public $collective;

    public function __construct(array $parameters)
    {
        $this->collective = ! empty($parameters) ? new Collection($parameters) : [];
    }


    public function __get($name)
    {
        if (property_exists($this, $name)) {
            return $this->{$name};
        }

        return $this->collective->{$name};
    }

}