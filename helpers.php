<?php

class _di {
    protected static $cont = array();

    public static function add_di($di)
    {
        if (! self::has_di($di)) {
            self::$cont[] = $di;
        }
    }

    public static function is_di_duplicated($di)
    {
        return count(self::$cont) > 0 && self::has_di($di);
    }

    public static function has_di($di)
    {
        return in_array($di, self::$cont);
    }

    public static function &di()
    {
        return self::$cont;
    }

    public static function last()
    {
        return end(self::$cont);
    }
}

if (! function_exists('render')) {
    function render($name)
    {

        if (file_exists("./view/$name" . ".php")) {
            require_once "./view/{$name}.php" ;
        } else {
            die("View is not found");
        }
    }
}

if (! function_exists('flatten')) {
    function flatten(array $array) {
        $return = array();
        array_walk_recursive($array, function($v, $k) use (&$return) { $return[$k] = $v; });
        return $return;
    }
}

if (! function_exists('dd')) {
    function dd(...$args) {
        return die(r($args));
    }
}

if (! function_exists('first')) {
    function first($array) {
        return array_key_exists(0, $array) ? $array[0] : [];
    }
}

if (! function_exists('array_in_key')) {
    function array_in_keys(array $arr, array $keys) {
        $temp = null;
        foreach ($arr as $key => $item) {
            foreach ($keys as $_key) {
                if ($key == $_key) {
                    $temp[] = $item;
                }
            }
        }

        return $temp;
    }
}