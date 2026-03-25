
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
                        "value" => "id"
                    ]
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
                    "label" => "bloco",
                    "type" => "select"
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
    ];
