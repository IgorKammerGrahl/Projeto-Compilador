<?php

function validateTableConsistency($jsonPath, $htmlPath)
{
    $jsonData = json_decode(file_get_contents($jsonPath), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Erro ao decodificar tabela.json: " . json_last_error_msg());
    }

    $dom = new DOMDocument();
    @$dom->loadHTMLFile($htmlPath);
    $rows = $dom->getElementsByTagName('tr');

    $htmlTokens = [];
    foreach ($rows as $row) {
        $cells = $row->getElementsByTagName('td');
        if ($cells->length > 1) {
            $token = trim($cells->item(1)->nodeValue);
            $htmlTokens[$token] = [];
            for ($i = 2; $i < $cells->length; $i++) {
                $htmlTokens[$token][] = trim($cells->item($i)->nodeValue);
            }
        }
    }

    foreach ($jsonData as $jsonToken) {
        $tokenName = $jsonToken['token'] ?? null;
        if ($tokenName && !isset($htmlTokens[$tokenName])) {
            echo "Token '{$tokenName}' encontrado no JSON, mas ausente no HTML.\n";
        }
    }

    foreach ($htmlTokens as $htmlToken => $transitions) {
        $found = false;
        foreach ($jsonData as $jsonToken) {
            if (($jsonToken['token'] ?? null) === $htmlToken) {
                $found = true;
                break;
            }
        }
        if (!$found) {
            echo "Token '{$htmlToken}' encontrado no HTML, mas ausente no JSON.\n";
        }
    }

    echo "Validação concluída.\n";
}

$jsonPath = 'caminho/para/tabela.json';
$htmlPath = 'caminho/para/tabela.html';

try {
    validateTableConsistency($jsonPath, $htmlPath);
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
?>
