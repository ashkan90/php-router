<?php


namespace Xav;


class HomeController extends Controller
{
    public function index($catch, $me, $to)
    {
        dd($catch, $me, $to);
        return render('home');
    }

}