<?php
class gerarFromDinamico
{
    private static $inforDate;

    private static function loadConfig()
    {
        if (!self::$inforDate)
            self::$inforDate = require_once __DIR__ . '/../../arrayTables.php';
    }
    public static function geraFrom($table, $id = null, $data = null, $user = null, $readonlyActive = false): string
    {
        self::loadConfig();
        $config =  self::$inforDate[$table] ?? null;
        if (!$config) return "Configuração não encontrada";
        $dados = [];
        $isRegistroBloqueado = false;
        $hoje = date('Y-m-d');
        $userPrivilegio = $user['privilegio'] ?? 'normal';
        $userId = $user['id'] ?? null;
        $dataPredefinida = null;
        if ($data && isset($data['ano'], $data['mes'], $data['dia'])) {
            $dataPredefinida = $data['ano'] . '-' .
                str_pad($data['mes'], 2, "0", STR_PAD_LEFT) . '-' .
                str_pad($data['dia'], 2, "0", STR_PAD_LEFT);
        }
        if ($id !== null) {
            $tabela = $config['tabela'] ?? null;
            $db = Database::connects();
            $stmt = $db->prepare("select * from $tabela where id=?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $dados = $result->fetch_assoc() ?? [];
            if ((isset($dados['dia']) && $dados['dia'] < $hoje) || $readonlyActive === true) {
                $isRegistroBloqueado = true;
            }
        } elseif ($readonlyActive === true) {
            $isRegistroBloqueado = true;
        }
        $coluneSelect = $config['colunas'] ?? null;
        $html = "";
        foreach ($coluneSelect as $coluna => $configColuna) {

            if (!empty($configColuna['primary'])) continue;
            $tipo = $configColuna['type'] ?? 'text';
            $campoBanco = $configColuna['maskname'] ?? $coluna;
            $valorBanco = $dados[$campoBanco] ?? '';
            if ($campoBanco === 'idUser' && $userPrivilegio === 'normal') {
                if ($id === null) {
                    $valorBanco = $userId;
                    $tipo = 'readonly_user';
                }
            }
            if (empty($valorBanco) && $tipo === 'date' && $dataPredefinida)
                $valorBanco = $dataPredefinida;
            $mostrarValor = $valorBanco;
            if (!empty($configColuna['relation']) && !empty($valorBanco)) {
                $rel = $configColuna['relation'];
                $db = Database::connects();
                $valorPasso = $valorBanco;

                if (isset($rel['tableConnection']) && is_array($rel['tableConnection'])) {
                    foreach ($rel['tableConnection'] as $passo) {
                        $t = $passo['tabela'];
                        $b = $passo['buscar'];
                        $o = $passo['onde'];

                        $sql = "SELECT $b FROM $t WHERE $o = ?";
                        $stmtRel = $db->prepare($sql);
                        $stmtRel->bind_param("s", $valorPasso);
                        $stmtRel->execute();
                        $resRel = $stmtRel->get_result()->fetch_assoc();

                        if ($resRel) {
                            $valorPasso = $resRel[$b];
                        } else {
                            $valorPasso = "Não encontrado";
                            break;
                        }
                    }
                    $mostrarValor = $valorPasso;
                } else {
                    // Relação simples de 1 nível
                    $sqlRel = "SELECT {$rel['coluna']} FROM {$rel['tabela']} WHERE {$rel['value']} = ?";
                    $stmtRel = $db->prepare($sqlRel);
                    $stmtRel->bind_param("s", $valorBanco);
                    $stmtRel->execute();
                    $resRel = $stmtRel->get_result()->fetch_assoc();
                    if ($resRel) {
                        $mostrarValor = $resRel[$rel['coluna']];
                    }
                }
            }
            $valorEscapado = htmlspecialchars($valorBanco);
            $mostrarEscapado = htmlspecialchars($mostrarValor);

            if ($tipo !== 'hidden') {
                $html .= "<label for='$coluna'>" . ucfirst($coluna) . "</label><br>";
            }
            $readonlyAttr = $isRegistroBloqueado ? "readonly style='cursor: not-allowed;'" : "";
            if ($tipo === 'readonly_user') {
                $nomeUsuario = $_SESSION['nome'] ?? 'Usuário Atual';
                $html .= "<input type='hidden' name='$coluna'   value='$valorEscapado'>";
                $html .= "<input type='text' id='$coluna' class='input-dados' value='$nomeUsuario' readonly>";
            } elseif ($tipo === 'date') {
                $minAttr = !$isRegistroBloqueado ? "min='$hoje'" : "";
                $html .= "<input type='date' name='$coluna' id='$coluna' class='input-dados' value='$valorEscapado' $readonlyAttr $minAttr>";
            } elseif ($tipo === "readonly") {
                $html .= "<input type='hidden' name='$coluna' value='$valorEscapado'>";
                $html .= "<input type='text' id='$coluna' class='input-dados' value='$mostrarEscapado' readonly style='cursor: not-allowed;'>";
            } elseif ($tipo === "select") {

                if ($isRegistroBloqueado) {
                    $html .= "<input type='hidden' name='$coluna' value='$valorEscapado'>";
                    $html .= "<input type='text' id='$coluna' class='input-dados' value='$mostrarEscapado' readonly style='cursor: not-allowed;' autocomplete='off'>";
                } else {
                    $html .= "<select class='select-dados' name='$coluna' id='$coluna'>";

                    // opções fixas
                    if (!empty($configColuna['options'])) {
                        foreach ($configColuna['options'] as $opt) {
                            $selected = ($opt == $valorBanco) ? "selected" : "";
                            $html .= "<option value='$opt' $selected>$opt</option>";
                        }
                    }

                    // relação com outra tabela
                    if (!empty($configColuna['relation'])) {
                        $rel = $configColuna['relation'];
                        $camposExtras = [];
                        foreach ($coluneSelect as $c => $conf) {
                            if (!empty($conf['depends']) && $conf['depends'] == $coluna) {
                                $camposExtras[] = $c;
                            }
                        }

                        $extraSQL = $camposExtras ? "," . implode(",", $camposExtras) : "";
                        $sql = "select {$rel['value']}, {$rel['coluna']} $extraSQL from {$rel['tabela']}";
                        $lista = Tabelas::list_All($sql);

                        while ($row = $lista->fetch_assoc()) {
                            $selected = ($row[$rel['value']] == $valorBanco) ? "selected" : "";
                            $dataAttributes = "";
                            $labelExtras = [];
                            foreach ($camposExtras as $campoExtra) {
                                if (isset($row[$campoExtra])) {
                                    $valExtra = htmlspecialchars($row[$campoExtra]);
                                    $dataAttributes .= " data-{$campoExtra}=\"{$valExtra}\"";
                                    $labelExtras[] = "{$campoExtra}: {$valExtra}";
                                }
                            }

                            $textoExtra = !empty($labelExtras) ? " | " . implode(" - ", $labelExtras) : "";
                            $html .= "<option value='{$row[$rel['value']]}' $selected $dataAttributes>";
                            $html .= htmlspecialchars($row[$rel['coluna']]) . $textoExtra;
                            $html .= "</option>";
                        }
                    }
                    $html .= "</select>";
                }
            } else {
                $html .= "<input type='$tipo' name='$coluna' id='$coluna' autocomplete='off' class='input-dados' value='$valorEscapado' $readonlyAttr>";
            }
            if ($tipo !== 'hidden' && $tipo !== 'readonly_user') $html .= "<br><br>";
            elseif ($tipo === 'readonly_user') $html .= "<br><br>";
        }
        return $html;
    }
}
