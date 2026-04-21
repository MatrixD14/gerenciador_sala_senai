<?php
return [
    "agendamentos" => [
        "orderna" => ['bloco', "dia", "usuario", "sala", "periodo", 'turmas'],
        "colunas" => [
            "idSala" => [
                "label" => "bloco",
                "type" => "select",
                "relation" => [
                    "tabela" => "sala",
                    "coluna" => "bloco",
                    "value" => "bloco",
                ],
            ],
            "dia" => [
                "label" => "Data do Agendamento",
                "type" => "date-range"
            ]
        ],
        "colunas_visiveis" => ["id", "usuario", 'turmas', "sala", "bloco", "dia", "hora_inicio", "hora_fim"]
    ],
    "salas" => [
        "orderna" => ["nome", 'bloco'],
        "colunas" => [
            "bloco" => [
                "label" => "Bloco",
                "type" => "select",
                "relation" => [
                    "tabela" => "sala",
                    "coluna" => "bloco",
                    "value" => "bloco"
                ]
            ]
        ],
        "colunas_visiveis" => ["id", 'nome', "bloco", "descricao"]
    ],
    "usuarios" => [
        "orderna" => ["nome", 'email'],
        "colunas" => [
            "privilegio" => [
                "label" => "privilegio",
                "type" => "select",
                "options" => ["admin", "aluno", "professor"],
            ]
        ],
        "colunas_visiveis" => ["id", 'nome', "email", "privilegio"]
    ],
    "menssagem" => [
        "orderna" => ['status', "remetente", "destinatario", 'sala'],
        "colunas" => [
            "agendado" => [
                "label" => "Data do Agendamento",
                "type" => "date-range"
            ]
        ],
        "colunas_visiveis" => ["id", 'status', 'enviado', 'remetente', "destinatario", "sala", 'agendado', "hora_inicio", "hora_fim", "mensagem"]
    ],
    'cursos' => [
        'orderna' => ['nome'],
        'colunas' => [],
        'colunas_visiveis' => ['id', 'nome', 'descricão']
    ],
    'turmas' => [
        'orderna' => ['nome', 'semestre', 'ano', 'turno', 'alunos'],
        'colunas' => [
            'turno' => [
                "label" => "turno",
                "type" => "select",
                'options' => [
                    'manhã' => 'Manhã',
                    'tarde' => 'Tarde',
                    'noite' => 'Noite'
                ]
            ]
        ],
        'colunas_visiveis' => ['id', 'nome', 'curso',  'semestre', 'ano', 'turno', 'alunos']
    ]
];
