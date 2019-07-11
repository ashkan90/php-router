<?php


namespace Xav;


class Router
{
    protected static $real;

    /**
     * @param string $pattern
     * @param $function
     * @throws \Exception
     */
    public static function get(string $pattern, $function)
    {
        self::$real = new Route($pattern, $function, __FUNCTION__);

        echo self::$real->run();
    }

}