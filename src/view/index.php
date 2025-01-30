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
        <h1>Analisador Léxico, Sintático e Semântico</h1>
        <form method="post">
            <label for="sourceCode">Digite o código fonte:</label>
            <textarea name="sourceCode" id="sourceCode"><?php echo isset($_POST['sourceCode']) ? htmlspecialchars($_POST['sourceCode']) : ''; ?></textarea>
            <button type="submit">Analisar</button>
        </form>

        <?php
        require_once __DIR__ . '/../config.php';
        require_once SEMANTICO_PATH . '/semantico.php';
        require_once LEXICO_PATH . '/lexer.php';
        require_once SINTATICO_PATH . '/parser.php';
        require_once LEXICO_PATH . '/token.php';

        use App\Lexico\Lexer;
        use App\Sintatico\Parser;
        use App\Semantico\Semantico;
        use App\Lexico\Token;

        // Caminho para os arquivos JSON
        $jsonLexicoPath = TABELA_JSON_PATH;
        $jsonSintaticoPath = TABELA_SINTATICA_JSON;

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['sourceCode'])) {
            $sourceCode = $_POST['sourceCode'];

            echo '<div class="output-section">';
            try {
                // Etapa 1: Análise Léxica
                $analisadorLexico = new Lexer($sourceCode, $jsonLexicoPath);
                $resultadoLexico = $analisadorLexico->analisar();
                $tokens = $resultadoLexico['tokens']; // Pegamos apenas os tokens
                $errosLexicos = $resultadoLexico['erros']; // Pegamos os erros léxicos

                echo "<h2>Tokens Reconhecidos:</h2><pre>";
                foreach ($tokens as $token) {
                    echo "Token: {$token->getName()}, Lexema: '{$token->getLexeme()}', Linha: {$token->getLine()}, Coluna: {$token->getInicio()}\n";
                }
                echo "</pre>";

                // Exibe erros léxicos se houver
                if (!empty($errosLexicos)) {
                    echo "<h2>Erros Léxicos:</h2><pre>";
                    foreach ($errosLexicos as $erro) {
                        echo "$erro\n";
                    }
                    echo "</pre>";
                }

                // Etapa 2: Análise Sintática
                $analisadorSintatico = new Parser($jsonSintaticoPath);
                $arvoreSintatica = $analisadorSintatico->analisar($tokens);

                echo "<h2>Árvore Sintática:</h2><pre>";
                echo $arvoreSintatica;
                echo "</pre>";

                // Etapa 3: Análise Semântica
                $analisadorSemantico = new Semantico();
                $analisadorSemantico->analisar($arvoreSintatica->getRaiz());

                echo "<h2>Resultado da Análise Semântica:</h2><pre>";
                echo "Análise semântica concluída com sucesso!";
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
