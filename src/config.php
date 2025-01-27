<?php
// Caminho base do projeto
define('BASE_PATH', realpath(__DIR__));

// Caminhos para os diretórios principais
define('LEXICO_PATH', BASE_PATH . DIRECTORY_SEPARATOR . 'analise' . DIRECTORY_SEPARATOR . 'lexico');
define('SINTATICO_PATH', BASE_PATH . DIRECTORY_SEPARATOR . 'analise' . DIRECTORY_SEPARATOR . 'sintatico');
define('TABELA_SINTATICA_PATH', BASE_PATH . DIRECTORY_SEPARATOR . 'dados' . DIRECTORY_SEPARATOR . 'tabelaSintatica');  // Alterado para o caminho correto
define('AUTOMATO_PATH', BASE_PATH . DIRECTORY_SEPARATOR . 'automato');

// Caminhos para arquivos específicos
// Caminho absoluto para o arquivo tabela.json
define('TABELA_JSON_PATH', __DIR__ . DIRECTORY_SEPARATOR . 'dados' . DIRECTORY_SEPARATOR . 'automato' . DIRECTORY_SEPARATOR . 'tabela.json');
define('TABELA_HTML_PATH', realpath(__DIR__ . '/dados/automato/tabela.html'));
define('TABELA_SINTATICA_HTML', TABELA_SINTATICA_PATH . DIRECTORY_SEPARATOR . 'tabelaSintatica.html');
define('TABELA_SINTATICA_JSON', TABELA_SINTATICA_PATH . DIRECTORY_SEPARATOR . 'tabelaSintatica.json');