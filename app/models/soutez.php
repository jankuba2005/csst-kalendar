<?php

namespace models;

class soutez extends \DB\Cortex
{
    protected $db = 'DB',
        $table = 'soutez';
    protected $fieldConf=[
        'nazev'=>[
            'type'=>'VARCHAR256',
            'required'=>true
        ],
        'datum'=>[
            'type'=>'DATE',
            'required'=>true,
            'index'=>true
        ],
        'misto'=>[
            'type'=>'VARCHAR128',
            'required'=>true
        ],
        'poradatel'=>[
            'type'=>'VARCHAR128',
            'required'=>true
        ],
        'vybaveni'=>[
            'type'=>'VARCHAR128',
            'values'=>['equipped', 'raw'],
            'required'=>true
        ],
        'kategorie'=>[
            'type'=>'VARCHAR128',
            'values'=>['druzstva', 'jednotlivci', 'univerzitni'],
            'required'=>true
        ],
        'discipliny'=>[
            'type'=>'JSON',
            'values'=>['trojboj', 'benchpress', 'mrtvy tah'],
            'required'=>true
        ],
        'popis'=>[
            'type'=>'TEXT',
            'required'=>false
        ]
    ];
}