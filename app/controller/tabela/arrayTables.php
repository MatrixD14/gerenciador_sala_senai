<?php
return [
    "agendamentos" => [
        "tabela" => "agendar_sala",
        "join" => "
            inner join usuario 
                on agendar_sala.idUser = usuario.id
            inner join sala 
                on agendar_sala.idSala = sala.id
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
                    "coluna" => "name",
                    "value" => "id"
                ]
            ],
            "sala"    => [
                'maskname' => 'idSala',
                'type' => 'select',
                'relation' => [
                    "tabela" => "sala",
                    "coluna" => "name",
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
                'options' => ['tarde', 'demanhã', 'noite']
            ]
        ],
        "especifico" => ["agendar_sala.id", "usuario.name as usuario", "sala.name as sala", "sala.bloco", "agendar_sala.dia", "agendar_sala.periodo"]
    ],
    "usuarios" => [
        "tabela" => "usuario",
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
            "name" => ['type' => 'text'],
            "email" => ['type' => 'email'],
            'previlegio' => ['type' => 'select', 'options' => ['admin', 'normal']]
        ]
    ],
    "salas" => [
        "tabela" => "sala",
        "dependencias" => [
            [
                "tabela" => "agendar_sala",
                "coluna" => "idSala",
                "link" => "agendamentos",
                "mensagem" => "agendamentos"
            ]
        ],
        "colunas" => ["id" => ['type' => 'number', 'primary' => true], "name" => ['type' => 'text'], "bloco" => ['type' => 'text'], "type" => ['type' => 'text']]
    ]
];
