<?php

class FormEngine
{
    private array $config;
    private array $dbData = [];
    private bool $isLocked = false;
    private array $user;
    private string $hoje;

    public function __construct(string $tableKey, $id = null, $user = null, $manualReadonly = false, array $presetData = [])
    {
        $allConfigs = require __DIR__ . '/../../../arrayTables.php';
        $this->config = $allConfigs[$tableKey] ?? throw new Exception("Tabela não configurada.");
        $this->user = $user ?? ['privilegio' => 'normal', 'id' => null];
        $this->hoje = date('Y-m-d');

        if ($id) {
            $this->loadData($id);
        } else {
            $this->dbData = $presetData;
        }

        // Regra de Bloqueio: Data passada ou flag manual
        $dataRef = $this->dbData['dia'] ?? null;
        if (($dataRef && $dataRef < $this->hoje) || $manualReadonly) {
            $this->isLocked = true;
        }
    }

    private function loadData($id)
    {
        $db = Database::connects();
        $tabela = $this->config['tabela'];
        $stmt = $db->prepare("SELECT * FROM $tabela WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $this->dbData = $stmt->get_result()->fetch_assoc() ?? [];
    }

    public function render(): string
    {
        $html = "";
        $dataRef = $this->dbData['dia'] ?? null;
        if ($this->isLocked && $dataRef && $dataRef < $this->hoje) {
            $html .= "
            <div class='alert-locked'>
                <span> <svg class='icon-alert'>
                            <use href='#icon-alentar'></use>
                        </svg></span>
                <div>
                    <strong>Registro Antigo:</strong> 
                    Esse item e do passado e não pode ser alterado.
                </div>
            </div>";
        }
        $slug = array_search($this->config, require __DIR__ . '/../../../arrayTables.php');
        foreach ($this->config['colunas'] as $name => $colConfig) {
            if ($colConfig['primary'] ?? false) continue;

            // Resolve o valor atual
            $fieldName = $colConfig['maskname'] ?? $name;
            $value = $this->dbData[$fieldName] ?? '';

            // Lógica especial para idUser em novos registros
            if ($fieldName === 'idUser' && empty($value) && $this->user['privilegio'] === 'normal') {
                $value = $this->user['id'];
                $colConfig['type'] = 'readonly_user';
            }
            $isHojeRegistro = ($this->dbData['dia'] ?? '') === $this->hoje;

            $html .= $this->renderRow($name, $colConfig, $value, $slug, $isHojeRegistro);
        }
        return $html;
    }

    private function renderRow($name, $col, $val, $slug, $isHoje): string
    {
        $label = ($col['type'] !== 'hidden') ? "<label for='$name'>" . ucfirst($name) . "</label><br>" : "";
        $field = FormRenderer::renderField($name, $col, $val, $this->isLocked, $this->config['colunas'], $isHoje, $slug);
        $spacing = ($col['type'] !== 'hidden') ? "<br><br>" : "";

        return "{$label}{$field}{$spacing}";
    }
    public function canSubmit(): bool
    {
        $dataRef = $this->dbData['dia'] ?? null;
        if (!$dataRef || $dataRef > $this->hoje) return false;
        return true;
    }
}
