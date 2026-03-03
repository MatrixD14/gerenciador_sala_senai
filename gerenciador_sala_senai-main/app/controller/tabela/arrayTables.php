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
        "colunas" => ["id", "usuario", "sala", "bloco", "dia", "periodo"],
        "especifico" => ["agendar_sala.id", "usuario.name as usuario", "sala.name as sala", "sala.bloco", "agendar_sala.dia", "agendar_sala.periodo"]
    ],
    "usuario" => [
        "tabela" => "usuario",
        "colunas" => ["id", "name", "email"]
    ],
    "salas" => [
        "tabela" => "sala",
        "colunas" => ["id", "name", "bloco", "type"]
    ]
];
