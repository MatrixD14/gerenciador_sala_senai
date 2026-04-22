<?php
return [
    "agendamentos" => [
        "tabela" => "agendar_sala",
        "owner_column" => "idUser",
        "no-repeat" => ["sala", 'bloco', "dia"],
        "dependencias" => [
            [
                "tabela" => "requisicoes_troca",
                "coluna" => "id_agendamento_revindicado",
                "link" => "Solicitacoes_de_troca",
                "Solicitacoes_de_troca" => "reivindicações"
            ]
        ],
        "join" => "
            inner join usuario 
                on agendar_sala.idUser = usuario.id
            inner join sala 
                on agendar_sala.idSala = sala.id
            inner join turmas 
                on agendar_sala.idTurma = turmas.id
        ",
        "colunas" => [
            "id" => [
                'type' => 'number',
                'primary' => true,
            ],
            "usuario" => [
                'maskname' => 'idUser',
                'type' => 'select',
                'relation' => [
                    "tabela" => "usuario",
                    "coluna" => "nome",
                    "value" => "id"
                ]
            ],
            'turmas' => [
                'maskname' => 'idTurma',
                'type' => 'select',
                'relation' => [
                    'tabela' => 'turmas',
                    'coluna' => 'nome',
                    'value' => 'id'
                ]
            ],
            "sala"    => [
                'maskname' => 'idSala',
                'type' => 'select',
                'relation' => [
                    "tabela" => "sala",
                    "coluna" => "nome",
                    "value" => "id"
                ]
            ],
            "bloco" =>  [
                'type' => 'hidden',
                'depends' => 'sala',
                'virtual' => true
            ],
            "dia" => [
                'type' => 'date',
                'ghost' => true,
            ],
            "hora_inicio" => [
                'type' => 'time',
            ],
            "hora_fim" => [
                'type' => 'time',
            ],
        ],
        "especifico" => ["agendar_sala.id", "usuario.nome as usuario", "turmas.nome as turmas", "sala.nome as sala", "sala.bloco", "agendar_sala.dia", "agendar_sala.hora_inicio", "agendar_sala.hora_fim"]
    ],
    "usuarios" => [
        "tabela" => "usuario",
        "owner_column" => "id",
        'no-repeat' => ["nome", "email"],
        "dependencias" => [
            [
                "tabela" => "agendar_sala",
                "coluna" => "idUser",
                "link" => "agendamentos",
                "mensagem" => "agendamentos"
            ]
        ],
        "colunas" => [
            "id" => ['type' => 'number', 'primary' => true],
            "nome" => ['type' => 'text'],
            "email" => ['type' => 'email',],

            'privilegio' => ['type' => 'select', 'options' => ['aluno', 'professor', 'admin']]
        ]
    ],
    "salas" => [
        "tabela" => "sala",
        'no-repeat' => ["nome", "bloco"],
        "dependencias" => [
            [
                "tabela" => "agendar_sala",
                "coluna" => "idSala",
                "link" => "agendamentos",
                "mensagem" => "agendamentos"
            ]
        ],
        "colunas" => [
            "id" => [
                'type' => 'number',
                'primary' => true
            ],
            "nome" => ['type' => 'text'],
            "bloco" => ['type' => 'text'],
            'capacidade' => ['type' => 'number'],
            "descricao" => ['type' => 'text']
        ]
    ],
    "Solicitacoes_de_troca" => [
        "tabela" => 'requisicoes_troca',
        'owner_relation' => [
            "tabela" => "agendar_sala",
            "coluna" => "id_agendamento_revindicado",
            "value" => "id",
            'owner_column' => "idUser"
        ],
        "join" => "
            INNER JOIN usuario user1
            ON requisicoes_troca.id_remetente = user1.id
            
            INNER JOIN agendar_sala
            ON requisicoes_troca.id_agendamento_revindicado = agendar_sala.id
            
            INNER JOIN usuario user2
            ON agendar_sala.idUser = user2.id
            
            INNER JOIN sala
            ON agendar_sala.idSala = sala.id
            ",

        "colunas" => [
            "id" => [
                "type" => "number",
                "primary" => true,
            ],
            "status" => [
                "type" => "hidden",
            ],
            "Enviado" => [
                "type" => "hidden",
            ],
            "remetente" => [
                "maskname" => "id_remetente",
                "type" => "readonly",
                "relation" => [
                    "tabela" => "usuario",
                    "coluna" => "nome",
                    "value" => 'id',
                ]

            ],
            "destinatario" => [
                "maskname" => "id_agendamento_revindicado",
                'type' => "readonly",
                "relation" => [
                    "tabela" => "agendar_sala",
                    "coluna" => "idUser",
                    "value" => 'id',
                    "tableConnection" => [
                        ["tabela" => "agendar_sala", 'buscar' => "idUser", "onde" => "id"],
                        ["tabela" => "usuario", "buscar" => "nome", "onde" => "id"]
                    ]
                ]
            ],
            "sala" => [
                "maskname" => "id_agendamento_revindicado",
                'type' => "readonly",
                "relation" => [
                    "tabela" => "sala",
                    "coluna" => "nome",
                    "value" => 'id',
                    "tableConnection" => [
                        ["tabela" => "agendar_sala", 'buscar' => "idSala", "onde" => "id"],
                        ["tabela" => "sala", "buscar" => "nome", "onde" => "id"]
                    ]
                ]
            ],
            "agendado" => [
                'type' => "hidden",
            ],
            "hora_inicio" => [
                "maskname" => "id_agendamento_revindicado",
                'type' => "readonly",
                "relation" => [
                    "tabela" => "sala",
                    "coluna" => "nome",
                    "value" => 'id',
                    "tableConnection" => [
                        ["tabela" => "agendar_sala", 'buscar' => "hora_inicio", "onde" => "id"],
                    ]
                ]
            ],
            "hora_fim" => [
                "maskname" => "id_agendamento_revindicado",
                'type' => "readonly",
                "relation" => [
                    "tabela" => "sala",
                    "coluna" => "nome",
                    "value" => 'id',
                    "tableConnection" => [
                        ["tabela" => "agendar_sala", 'buscar' => "hora_fim", "onde" => "id"],
                    ]
                ]
            ],
            "mensagem" => [
                "maskname" => "mensagem",
                "type" => "readonly"
            ],
        ],
        "especifico" => [
            'requisicoes_troca.id',
            'requisicoes_troca.status',
            "requisicoes_troca.data_envio as Enviado",
            "user1.nome as remetente",
            "user2.nome as destinatario",
            "sala.nome as sala",
            "agendar_sala.dia as agendado",
            "agendar_sala.hora_inicio",
            "agendar_sala.hora_fim",
            "requisicoes_troca.mensagem"
        ],
    ],
    "cursos" => [
        'tabela' => 'cursos',
        'colunas' => [
            "id" => [
                "type" => "number",
                "primary" => true,
            ],
            'nome' => [
                'type' => 'select',
                'relation' => [
                    'tabela' => 'cursos',
                    'coluna' => 'nome',
                    'value' => 'id',
                ],
            ],
            'descricao' => [
                'type' => 'text'
            ]
        ]
    ],
    'turmas' => [
        'tabela' => 'turmas',
        'join' => '
            inner join cursos
            on turmas.idCurso = cursos.id
        ',
        'colunas' => [
            "id" => [
                "type" => "number",
                "primary" => true,
            ],
            'nome' => [
                'type' => 'select',
                'relation' => [
                    'tabela' => 'turmas',
                    'coluna' => 'nome',
                    'value' => 'id',
                ],
            ],
            'curso' => [
                'maskname' => 'idCurso',
                'type' => 'select',
                'relation' => [
                    'tabela' => 'cursos',
                    'coluna' => 'nome',
                    'value' => 'id'
                ]
            ],
            'semestre' => [
                'type' => 'text',
            ],
            "ano" => [
                'type' => 'hidden',
                'depends' => 'semestre',
                'virtual' => true
            ],
            'turno' => [
                'type' => 'select',
                'options' => [
                    'tarde',
                    'noite',
                    'manha'
                ]
            ],
            'alunos' => [
                'maskname' => 'quantidade',
                'type' => 'number'
            ]
        ],
        'especifico' => [
            'turmas.id',
            'turmas.nome',
            'cursos.nome as curso',
            'turmas.ano as ano',
            'turmas.semestre as semestre',
            'turmas.turno as turno',
            'turmas.quantidade as alunos'
        ]
    ]
];
