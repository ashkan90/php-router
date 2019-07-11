<?php


namespace Xav;


trait InteractInput
{
    public function all()
    {
        return $this->cast($this->input(), Collection::class);
    }

    public function toArray()
    {
        return (array) $this->all()->items;
    }

    protected function cast($instance, $className)
    {
        return unserialize(sprintf(
            'O:%d:"%s"%s',
            \strlen($className),
            $className,
            strstr(strstr(serialize($instance), '"'), ':')
        ));
    }
}