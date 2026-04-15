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
        "confirma" => ["type" => "icon-afirma", 'menssage' => 'ele serve para aceita'],
        "filtro" => ["type" => "icon-iconMutidirecao", 'menssage' => 'ele serve para aceita'],
        "download" => ["type" => "icon-setaMultidirecao", 'menssage' => "fazer download de file"]
    ],

    "agendamentos" => [
        "admin" => [
            'view',
            'agenda',
            'reload',
            "edite",
            'delete',
            "filtro",
            "download"
        ],
        "professor" => [
            'view',
            'agenda',
            'reload',
            "edite",
            'revindicar',
            'delete',
            "filtro",
            "download"
        ],
        "aluno" => [
            'view',
            'agenda',
            'reload',
            "edite",
            'revindicar',
            'delete',
            "filtro"
        ]
    ],
    "usuarios" => [
        "admin" => [
            'view',
            'add',
            'reload',
            "edite",
            'delete',
            "filtro",
            "download"
        ],

    ],
    "salas" => [
        "admin" => [
            'view',
            'add',
            'reload',
            "edite",
            'delete',
            "filtro",
            "download"
        ],
        "professor" => [
            'view',
            'reload',
            "filtro"
        ],
        "aluno" => [
            'view',
            'reload',
            "filtro"
        ]
    ],
    "menssagem" => [
        "admin" => [
            'view',
            'reload',
            'delete',
            "filtro"
        ],
        "professor" => [
            'view',
            'reload',
            "confirma",
            "filtro"
        ],
        "aluno" => [
            'view',
            'reload',
            "confirma",
            "filtro"
        ]
    ],
    'cursos' => [
        "admin" => [
            'view',
            'add',
            'reload',
            "edite",
            'delete',
            "filtro",
            "download"
        ],
        "professor" => [
            'view',
            'reload',
            "filtro"
        ],
        "aluno" => [
            'view',
            'reload',
            "filtro"
        ]
    ],
    'turmas' => [
        "admin" => [
            'view',
            'add',
            'reload',
            "edite",
            'delete',
            "filtro",
            "download"
        ],
        "professor" => [
            'view',
            'reload',
            "filtro"
        ],
        "aluno" => [
            'view',
            'reload',
            "filtro"
        ]
    ]
];
