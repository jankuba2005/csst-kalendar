<?php

namespace controllers;
class install

{
    public function setup()
    {
        \models\soutez::setdown();
        \models\soutez::setup();

        \Base::instance()->reroute('/');
    }
}