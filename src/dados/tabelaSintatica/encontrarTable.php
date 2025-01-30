<?php

// Caminho para o arquivo tabelaSintatica.json
$jsonFilePath = "tabelaSintatica.json";

// Carregar o JSON
$jsonData = file_get_contents($jsonFilePath);
$data = json_decode($jsonData, true);

// Verificar se a actionTable existe
if (!isset($data["actionTable"])) {
    die("Erro: actionTable não encontrada no JSON.");
}

$actionTable = $data["actionTable"];

// Percorrer a actionTable para encontrar o índice correto
foreach ($actionTable as $index => $action) {
    if (isset($action["ID"]) && isset($action["ID"]["state"]) && $action["ID"]["state"] == 43) {
        echo "Entrada encontrada no índice {$index}:\n";
        print_r($action);
    }
}

?>
