<?php

namespace controllers;

class index
{
    public function index(\Base $base)
    {
        echo \Template::instance()->render('index.html');
    }

}