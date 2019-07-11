<?php


namespace Xav;


class Controller
{
    public function callAction($method, $params)
    {
        return call_user_func_array([$this, $method], $params);
    }
}