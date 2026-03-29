
   <?php
    return [
        "agendamentos" => [
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
                "periodo" => [
                    "label" => "Período",
                    "type" => "select",
                    "options" => ["manhã", "tarde", "noite"]
                ],
                "dia" => [
                    "label" => "Data do Agendamento",
                    "type" => "date-range"
                ]
            ],
            "colunas_visiveis" => ["id", "usuario", "sala", "bloco", "dia", "periodo"]
        ],
        "salas" => [
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
            "colunas_visiveis" => ["id", 'name', "bloco", "descricao"]
        ],
        "usuarios" => [
            "colunas" => [
                "privilegio" => [
                    "label" => "privilegio",
                    "type" => "select",
                    "options" => ["admin", "normal"],
                ]
            ],
            "colunas_visiveis" => ["id", 'name', "email", "privilegio"]
        ],
        "menssagem" => [
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
    ];
