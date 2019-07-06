<?php


namespace AST;


class HomeController extends Controller
{
    public function __construct()
    {
        $this->index();
    }

    public function index()
    {
        return render('home');
    }

}