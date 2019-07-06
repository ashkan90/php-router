<?php


namespace AST;


class Router extends Facade
{
    protected static $real;

    /**
     * @param string $pattern
     * @param $function
     * @throws \Exception
     */
    public static function get(string $pattern, $function)
    {
        self::$real = new RR($pattern, $function, __FUNCTION__);

        echo self::$real->run();
    }

}