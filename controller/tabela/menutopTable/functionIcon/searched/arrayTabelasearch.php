<?php
return [
    "agendamentos" => [
        "searchable" => [
            "usuario.name",
            "sala.name",
            "sala.bloco",
            "agendar_sala.periodo"
        ]
    ],
    "salas" => [
        "searchable" => ["name", "bloco", "descricao"]
    ],

    "usuarios" => [
        "searchable" => ["name", "email", "privilegio"]
    ],
    "menssagem" => [
        "searchable" => [
            "user1.name",
            "user2.name",
            "sala.name",
            "agendar_sala.periodo"
        ]
    ],
];
