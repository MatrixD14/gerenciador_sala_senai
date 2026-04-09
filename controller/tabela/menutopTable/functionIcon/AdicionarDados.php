<?php
class Inserted
{
    private static $inforDate;

    private static function loadConfig()
    {
        if (!self::$inforDate)
            self::$inforDate = require_once __DIR__ . '/../../arrayTables.php';
    }
    public static function inserted()
    {
        date_default_timezone_set('America/Sao_Paulo');

        self::loadConfig();
        $table = $_POST['table'] ?? null;
        if (!$table) return "Dados insuficientes para editar.";
        $config =  self::$inforDate[$table] ?? null;
        if (!$config) return "Configuração da tabela não encontrada.";
        $tabela = $config['tabela'] ?? null;
        $coluneSelect = $config['colunas'] ?? null;
        if ($coluneSelect === null) return "Configuração de colunas não encontrada.";
        $dataEnvio = $_POST['dia'] ?? $_POST['data'] ?? null;
        $periodoEnvio = strtolower($_POST['periodo'] ?? '');

        if ($dataEnvio && $config) {
            $hoje = date('Y-m-d');
            $horaAtual = (int)date('H');
            $regras = $config['colunas']['periodo']['options'] ?? [];

            if ($dataEnvio < $hoje) {
                Tabelas::log_error_table("Erro: Não é possível agendar em datas passadas.");
                header("Location: /$table");
                exit;
            }

            if ($dataEnvio === $hoje && isset($regras[$periodoEnvio])) {
                if ($horaAtual >= $regras[$periodoEnvio]['max']) {
                    Tabelas::log_error_table("Erro: O período $periodoEnvio encerrou às " . $regras[$periodoEnvio]['max'] . "h.");
                    header("Location: /$table");
                    exit;
                }
            }
        }
        if (isset($config['no-repeat']) && is_array($config['no-repeat'])) {
            $whereClauses = [];
            $params = [];
            $types = "";

            foreach ($config['no-repeat'] as $colunaRef) {
                // Pegamos a configuração da coluna para saber o maskname (nome no banco)
                $colConfig = $coluneSelect[$colunaRef] ?? null;
                if (!$colConfig) continue;

                $nomeColunaBanco = $colConfig['maskname'] ?? $colunaRef;
                $valorPost = $_POST[$colunaRef] ?? '';

                if ($valorPost !== '') {
                    $whereClauses[] = "$nomeColunaBanco = ?";
                    $params[] = $valorPost;
                    $types .= (isset($colConfig['relation']) || ($colConfig['type'] ?? '') === 'number') ? "i" : "s";
                }
            }

            if (!empty($whereClauses)) {
                $db = Database::connects();
                $sqlCheck = "SELECT id FROM $tabela WHERE " . implode(" AND ", $whereClauses);
                $stmtCheck = $db->prepare($sqlCheck);
                $stmtCheck->bind_param($types, ...$params);
                $stmtCheck->execute();

                if ($stmtCheck->get_result()->num_rows > 0) {
                    Tabelas::log_error_table("Erro: Já existe um registro com estes dados em $table (Duplicidade bloqueada).");
                    header("Location: /$table");
                    exit;
                }
            }
        }
        $colunasBanco = [];
        $placeholders = [];
        $valores = [];
        $tipos = "";

        foreach ($coluneSelect as $nomeNoForm => $conf) {
            if (!empty($conf['primary']) || !empty($conf['virtual'])) continue;
            $nomeColunaBanco = $conf['maskname'] ?? $nomeNoForm;
            $valor = isset($_POST[$nomeNoForm]) ? trim($_POST[$nomeNoForm]) : "";

            if ($valor === "" && !isset($conf['optional'])) {
                Tabelas::log_error_table("Erro: O campo " . ucfirst($nomeNoForm) . " é obrigatório e não foi preenchido.");
                header("Location: /$table");
                exit;
            }
            $colunasBanco[] = $nomeColunaBanco;
            $placeholders[] = "?";
            $valores[] = $valor;
            $tipos .= (($conf['type'] ?? '') === 'number' || (!empty($conf['relation']))) ? "i" : "s";
        }

        if (empty($colunasBanco)) return "Nenhum dado enviado para inserção.";

        $db = Database::connects();
        $sql = "insert into $tabela (" . implode(", ", $colunasBanco) . ") values (" . implode(", ", $placeholders) . ")";
        $stmt = $db->prepare($sql);
        if (!$stmt) {
            Tabelas::log_error_table("Erro no Prepare: " . $db->error);
            header("Location: /$table");
            exit;
        }
        $stmt->bind_param($tipos, ...$valores);

        if ($stmt->execute()) {
            $novoId = $db->insert_id;
            Tabelas::log_error_table("Inserido com sucesso na tabela $tabela, Novo ID: $novoId");
        } else {
            Tabelas::log_error_table("Erro ao inserir na tabela: " . $stmt->error);
        }
        header("Location: /$table");
        exit;
    }
}
