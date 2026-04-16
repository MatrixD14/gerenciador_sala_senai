
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
            "orderna" => ["remetente", "destinatario", 'sala'],
            "colunas" => [
                "id_agendamento_revindicado" => [
                    "label" => "periodo",
                    "type" => "select",
                    'options' => [
                        'manhã' => ["min" => 5, "max" => 7],
                        'tarde' => ["min" => 7, "max" => 11],
                        'noite' => ["min" => 7, "max" => 18]
                    ]
                ]
            ],
            "colunas_visiveis" => ["id", 'remetente', "destinatario", "sala", "periodo", "menssagem"]
        ],
        'cursos' => [
            'orderna' => ['nome'],
            'colunas' => [],
            'colunas_visiveis' => ['id', 'nome', 'descricão']
        ],
        'turmas' => [
            'orderna' => ['nome', 'semestre', 'turno', 'alunos'],
            'colunas' => [],
            'colunas_visiveis' => ['id', 'turma', 'curso',  'semestre', 'turno', 'alunos']
        ]
    ];
