<?php
return [
    "agendamentos" => [
        "tabela" => "agendar_sala",
        "owner_column" => "idUser",
        "no-repeat" => ["sala", 'bloco', "dia", 'periodo'],
        "dependencias" => [
            [
                "tabela" => "revindicados",
                "coluna" => "id_agendamento_revindicado",
                "link" => "menssagem",
                "mensagem" => "reivindicações"
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
            "dia" => ['type' => 'date'],
            "periodo" => [
                'type' => 'select',
                'options' => [
                    'manhã' => ["min" => 5, "max" => 7],
                    'tarde' => ["min" => 7, "max" => 11],
                    'noite' => ["min" => 7, "max" => 18]
                ]
            ],

        ],
        "especifico" => ["agendar_sala.id", "usuario.nome as usuario", "turmas.nome as turmas", "sala.nome as sala", "sala.bloco", "agendar_sala.dia", "agendar_sala.periodo"]
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
            'privilegio' => ['type' => 'select', 'options' => ['normal', 'admin']]
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
        "colunas" => ["id" => ['type' => 'number', 'primary' => true], "nome" => ['type' => 'text'], "bloco" => ['type' => 'text'], "descricao" => ['type' => 'text']]
    ],
    "menssagem" => [
        "tabela" => 'revindicados',
        'owner_relation' => [
            "tabela" => "agendar_sala",
            "coluna" => "id_agendamento_revindicado",
            "value" => "id",
            'owner_column' => "idUser"
        ],
        "join" => "
            INNER JOIN usuario user1
            ON revindicados.id_remetente = user1.id
            
            INNER JOIN agendar_sala
            ON revindicados.id_agendamento_revindicado = agendar_sala.id
            
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
                    "tabela" => "usuario",
                    "coluna" => "nome",
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
            "periodo" => [
                "maskname" => "id_agendamento_revindicado",
                'type' => "readonly",
                "relation" => [
                    "tabela" => "sala",
                    "coluna" => "nome",
                    "value" => 'id',
                    "tableConnection" => [
                        ["tabela" => "agendar_sala", 'buscar' => "periodo", "onde" => "id"],
                    ]
                ]
            ],
            "menssagem" => [
                "maskname" => "mensagem",
                "type" => "readonly"
            ]

        ],
        "especifico" => [
            'revindicados.id',
            'revindicados.status as reivindicado',
            "revindicados.data_envio as Enviado",
            "user1.nome as rementente",
            "user2.nome as destinatario",
            "sala.nome as sala",
            "agendar_sala.dia as agendado",
            "agendar_sala.periodo",
            "revindicados.mensagem"
        ],
    ]
];
