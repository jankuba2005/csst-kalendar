<?php

namespace controllers;

class souteze
{
    public function postAdd(\Base $base)
    {
        $soutez = new \models\soutez();
        $soutez->copyfrom($base->get('POST'));
        $soutez->save();
        $base->reroute('/');
    }

    public function getAdd(\Base $base)
    {
        $base->set('title', 'Přidat soutěž');
        $base->set('content', 'soutez/add.html');
        echo \Template::instance()->render('index.html');
    }

    public function getList(\Base $base)
    {
        $soutez = new \models\soutez();
        $base->set('soutez', $soutez->find());
        $base->set('title', 'Seznam soutěží');
        $base->set('content', 'soutez/list.html');
        echo \Template::instance()->render('index.html');
    }
}