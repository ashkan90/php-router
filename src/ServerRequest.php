<?php


namespace Xav;


class ServerRequest implements Access
{
    use InteractInput;

    protected $attributes;

    protected $baseUrl;

    protected $method;

    public function __construct($request, $options = [])
    {
        $this->attributes = new Parameters($request);

        $this->method = strtoupper($options['method']);
        $this->baseUrl = $options['url'];

        $this->propSerializer();
    }

    public function input()
    {
        return $this->attributes->collective;
    }

    public function get($key)
    {
        return $this->__get($key);
    }

    protected function propSerializer()
    {
        foreach ($this->toArray() as $key => $item) {
            $this->{$key} = $item;
        }
    }

    /**
     * @param $name
     * @return mixed
     * @throws \Exception
     */
    public function __get($name)
    {
        if (property_exists($this, $name)) {
            return $this->{$name};
        }

        if (! is_null($this->attributes)) {
           return $this->attributes->{$name};
        }

        throw new \Exception("??");
    }
}