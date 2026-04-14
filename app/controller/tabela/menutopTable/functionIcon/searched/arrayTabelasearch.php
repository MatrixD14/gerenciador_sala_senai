<?php
return [
    "agendamentos" => [
        "searchable" => [
            "usuario.nome",
            "sala.nome",
            "sala.bloco",
            "agendar_sala.periodo"
        ]
    ],
    "salas" => [
        "searchable" => ["nome", "bloco", "descricao"]
    ],

    "usuarios" => [
        "searchable" => ["nome", "email", "privilegio"]
    ],
    "menssagem" => [
        "searchable" => [
            "user1.nome",
            "user2.nome",
            "sala.nome",
            "agendar_sala.periodo"
        ]
    ],
];
