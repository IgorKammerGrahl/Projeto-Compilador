<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analisador Léxico e Sintático</title>
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f0f2f5;
            color: #333;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .container {
            background: #ffffff;
            padding: 20px 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            width: 100%;
        }

        h1 {
            text-align: center;
            color: #4CAF50;
            font-size: 24px;
            margin-bottom: 20px;
        }

        label {
            font-weight: bold;
            margin-bottom: 10px;
            display: inline-block;
        }

        textarea {
            width: 100%;
            height: 200px;
            border: 1px solid #ccc;
            border-radius: 5px;
            padding: 10px;
            resize: vertical;
            font-family: monospace;
            font-size: 14px;
            box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #45a049;
        }

        pre {
            background-color: #f7f9fc;
            padding: 15px;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-family: monospace;
            font-size: 14px;
            margin-top: 20px;
            overflow-x: auto;
        }

        .output-section h2 {
            color: #4CAF50;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Analisador Léxico e Sintático</h1>
        <form method="post">
            <label for="sourceCode">Digite o código fonte:</label>
            <textarea name="sourceCode" id="sourceCode"><?php echo isset($_POST['sourceCode']) ? htmlspecialchars($_POST['sourceCode']) : ''; ?></textarea>
            <button type="submit">Analisar</button>
        </form>

        <?php
        require_once __DIR__ . '/../../vendor/autoload.php';
        require_once __DIR__ . '/../config.php';
        require_once LEXICO_PATH . '/lexer.php';
        require_once SINTATICO_PATH . '/parser.php';
        require_once LEXICO_PATH . '/token.php';

        use App\Lexico\lexer;
        use App\Sintatico\parser;
        use App\Lexico\Token;

        // Caminho para os arquivos JSON
        $jsonLexicoPath = TABELA_JSON_PATH;
        $jsonSintaticoPath = TABELA_SINTATICA_JSON;

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['sourceCode'])) {
            $sourceCode = $_POST['sourceCode'];

            echo '<div class="output-section">';
            try {
                // Exibe o caminho do arquivo JSON para depuração
                echo "Caminho do arquivo JSON: " . $jsonLexicoPath . "<br>";
                
                // Verifica se o arquivo tabela.json existe
                if (!file_exists($jsonLexicoPath)) {
                    echo "Erro: o arquivo tabela.json não foi encontrado em {$jsonLexicoPath}.<br>";
                } else {
                    echo "Arquivo encontrado: {$jsonLexicoPath}.<br>";
                }

                // Etapa 1: Análise Léxica
                $analisadorLexico = new lexer($sourceCode, $jsonLexicoPath);
                $tokens = $analisadorLexico->analisar();

                echo "<h2>Tokens Reconhecidos:</h2><pre>";
                foreach ($tokens as $token) {
                    echo "Token: {$token->getName()}, Lexema: '{$token->getLexeme()}', Linha: {$token->getLine()}, Coluna: {$token->getInicio()}\n";
                }
                echo "</pre>";

                // Etapa 2: Análise Sintática
                $analisadorSintatico = new parser($jsonSintaticoPath);
                $resultado = $analisadorSintatico->analisar($tokens);

                echo "<h2>Resultado da Análise Sintática:</h2><pre>";
                echo $resultado ? "Análise sintática concluída com sucesso!" : "Erro de sintaxe encontrado.";
                echo "</pre>";
            } catch (Exception $e) {
                echo "<h2>Erro</h2><pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
            }
            echo '</div>';
        }
        ?>
    </div>
</body>
</html>
