
   <?php
    return [
        "agendamentos" => [
            "campos" => [
                "idSala" => [
                    "label" => "Salas",
                    "type" => "checkbox-group",
                    "tabela" => "sala",
                    "coluna" => "name",
                    "value" => "id"
                ],
                "periodo" => [
                    "label" => "Período",
                    "type" => "checkbox-group",
                    "options" => ["manhã", "tarde", "noite"]
                ],
                "dia" => [
                    "label" => "Data do Agendamento",
                    "type" => "date-range"
                ]
            ],
            "colunas_visiveis" => [
                "options" => ["id", "usuario", "sala", "bloco", "dia", "periodo"]
            ]
        ]
    ];
