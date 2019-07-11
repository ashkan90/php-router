<?php


namespace Xav;


use Closure;

trait Macroable
{
    protected static $macros ;

    public static function macro($name, callable $macro)
    {
        static::$macros[$name] = $macro;
    }

    public static function hasMacro($name)
    {
        return isset(static::$macros[$name]);
    }

    public function __call($method, $parameters)
    {
        if (! static::hasMacro($method)) {
            throw new \Exception("Method {$method} does not exist.");
        }
        if (static::$macros[$method] instanceof Closure) {
            return call_user_func_array(static::$macros[$method]->bindTo($this, static::class), $parameters);
        }
        return call_user_func_array(static::$macros[$method], $parameters);
    }
}