<?php
return [
    "acoes" => [
        "view" => ['type' => "icon-lupa", 'menssage' => 'pesquisar na table'],
        "add" => ['type' => "icon-mais", 'menssage' => 'adicionar usuario'],
        "reload" => ['type' => "icon-reload", 'menssage' => 'atualizar a tabela'],
        "edite" => ['type' => "icon-lapiz", 'menssage' => 'editar dados'],
        "delete" => ['type' => "icon-lixeira", 'menssage' => 'deletar itens'],
        "agenda" => ['type' => 'icon-anotacao', 'menssage' => 'agendar'],
        "reivindicar" => ['type' => 'icon-megafone', 'menssage' => 'negociar com o outro usuario que quer usar esse dia'],
        "confirma" => ["type" => "icon-afirma", 'menssage' => 'ele serve para aceita'],
        "filtro" => ["type" => "icon-iconMutidirecao", 'menssage' => 'filtra a tabela para deter uma visao mais eficiente para dados especifico'],
        "ViewInPDF" => ["type" => "icon-setaMultidirecao", 'menssage' => "ver o pdf "]
    ],

    "agendamentos" => [
        "admin" => [
            'view',
            'agenda',
            'reload',
            "edite",
            'delete',
            "filtro",
            "ViewInPDF"
        ],
        "professor" => [
            'view',
            'agenda',
            'reload',
            "edite",
            'reivindicar',
            'delete',
            "filtro",
            "ViewInPDF"
        ],
        "aluno" => [
            'view',
            'agenda',
            'reload',
            "edite",
            'reivindicar',
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
            "ViewInPDF"
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
            "ViewInPDF"
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
    "Solicitacoes_de_troca" => [
        "admin" => [
            'view',
            'reload',
            "filtro",
            "ViewInPDF"
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
            "ViewInPDF"
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
            "ViewInPDF"
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
