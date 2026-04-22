<?php
return [
    "agendamentos" => [
        "searchable" => [
            "usuario.nome",
            "sala.nome",
            "sala.bloco",
            "agendar_sala.hora_inicio",
            "agendar_sala.hora_fim"
        ]
    ],
    "salas" => [
        "searchable" => ["nome", "bloco", "descricao"]
    ],

    "usuarios" => [
        "searchable" => ["nome", "email", "privilegio"]
    ],
    "Solicitacoes_de_troca" => [
        "searchable" => [
            "user1.nome",
            "user2.nome",
            "sala.nome",
            "agendar_sala.dia",
            "agendar_sala.hora_inicio",
            "agendar_sala.hora_fim",
        ]
    ],
    'cursos' => [
        "searchable" => ["nome"]
    ],
    'turmas' => [
        "searchable" => [
            'turmas.nome',
            'cursos.nome',
            'turmas.ano',
            'turmas.semestre ',
            'turmas.turno',
            'turmas.quantidade'
        ]
    ]
];
