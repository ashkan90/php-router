<?php

require "vendor/autoload.php";
require "helpers.php";

//\Xav\Route::set('DELIMITER', '?');
//\Xav\Router::get('/gel', 'HomeController:index');
\Xav\Router::get('/:id/:name/ananın/:dene', function ($id, $name, $dene) {
    dd($id, $name, $dene);
});

\Xav\Router::get('/welcome', function () {
    return "welcome";
});

\Xav\Router::get('/teee', function () {
    return "merhaba berkay ";
});
