<?php


namespace Xav;


trait Configurable
{
    /**
     * @param $key
     * @param $newValue
     * @param object|null $class
     * @throws \ReflectionException
     */
    public static function set($key, $newValue, object $class = null)
    {
        $instance = $class ? $class : __CLASS__;

        dd($instance);
//        $instance::{$key} = $newValue;

//        self::isOwnStaticProperty($key, $instance) ? $instance::{$key} = $newValue : $instance->{$key} = $newValue;
    }

    // TODO:
    public static function instantiation($instance)
    {

    }

    /**
     * @param $property
     * @param $owner
     * @return bool
     * @throws \ReflectionException
     */
    protected static function isOwnStaticProperty($property, $owner)
    {
        $reflect = new \ReflectionClass($owner);

        $props = $reflect->getProperties(\ReflectionProperty::IS_STATIC);
        foreach ($props as $prop) {
            if ($prop->class == $owner && $prop->name == $property) {
                return true;
            }
        }

        return false;
    }
}