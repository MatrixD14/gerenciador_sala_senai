<?php
return [
    "acoes" => [
        "view" => ['type' => "icon-lupa", 'menssage' => 'pesquisar na table'],
        "add" => ['type' => "icon-mais", 'menssage' => 'adicionar usuario'],
        "reload" => ['type' => "icon-reload", 'menssage' => 'atualizar a tabela'],
        "edite" => ['type' => "icon-lapiz", 'menssage' => 'editar dados'],
        "delete" => ['type' => "icon-lixeira", 'menssage' => 'deletar itens'],
        "agenda" => ['type' => 'icon-anotacao', 'menssage' => 'agendar'],
        "revindicar" => ['type' => 'icon-megafone', 'menssage' => 'negociar com o outro usuario que quer usar esse dia'],
        "confirma" => ["type" => "icon-afirma", 'menssage' => 'ele serve para aceita']
    ],

    "agendamentos" => [
        "admin" => [
            'view',
            'agenda',
            'reload',
            "edite",
            'delete',
        ],
        "normal" => [
            'view',
            'reload',
            'revindicar'
        ]
    ],
    "usuarios" => [
        "admin" => [
            'view',
            'add',
            'reload',
            "edite",
            'delete'
        ],
        "normal" => []
    ],
    "salas" => [
        "admin" => [
            'view',
            'add',
            'reload',
            "edite",
            'delete'
        ],
        "normal" => [
            'view',
            'reload'
        ]
    ],
    "menssagem" => [
        "admin" => [
            'view',
            'reload',
            "confirma",
            'delete'
        ],
        "normal" => [
            'view',
            'reload',
            "confirma"
        ]
    ]
];
