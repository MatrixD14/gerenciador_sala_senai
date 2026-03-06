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
            "id" => 'number',
            "usuario" => ["tabela" => "usuario", "coluna" => "name", "campo_real" => "idUser"],
            "sala"    => ["tabela" => "sala",    "coluna" => "name", "campo_real" => "idSala"],
            "bloco" =>  ["tabela" => "sala", "coluna" => "bloco", "visual" => true],
            "dia" => 'date',
            "periodo" => ['tarde', 'demanhã', 'noite']
        ],
        "especifico" => ["agendar_sala.id", "usuario.name as usuario", "sala.name as sala", "sala.bloco", "agendar_sala.dia", "agendar_sala.periodo"]
    ],
    "usuarios" => [
        "tabela" => "usuario",
        "colunas" => ["id" => 'number', "name" => 'text', "email" => 'email', 'previlegio' => ['admin', 'normal']]
    ],
    "salas" => [
        "tabela" => "sala",
        "colunas" => ["id" => 'number', "name" => 'text', "bloco" => 'text', "type" => 'text']
    ]
];
