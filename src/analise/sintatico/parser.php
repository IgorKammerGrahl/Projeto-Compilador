<?php

namespace App\Sintatico;

use App\Lexico\Token;
use Exception;

class Parser {
    private array $gotoTable;
    private array $actionTable;
    
    private array $producoes = [
        /* Regra 0 */ ["<PROGRAMA>", ["MAIN", "ABRE_PAR","FECHA_PAR", "ABRE_CHAVES", "<LISTA_COMANDOS>", "FECHA_CHAVES"]],
        /* Regra 1 */ ["<PROGRAMA>", ["<DECLARACAO_GLOBAL>", "<PROGRAMA>"]],
        
        // Lista de comandos
        /* Regra 2 */ ["<DECLARACAO_GLOBAL>", ["<DECLARACAO>", "PV"]],  // ← Declarações fora de funções
        /* Regra 3 */ ["<LISTA_COMANDOS_REST>", ["<COMANDO>", "<LISTA_COMANDOS_REST>"]],
        /* Regra 4 */ ["<LISTA_COMANDOS_REST>", []], // ε
        
        // Comandos básicos
        /* Regra 5 */ ["<COMANDO>", ["<DECLARACAO>"]],
        /* Regra 6 */ ["<COMANDO>", ["<ATRIBUICAO>"]],
        /* Regra 7 */ ["<COMANDO>", ["<LEITURA>"]],
        /* Regra 8 */ ["<COMANDO>", ["<IMPRESSAO>"]],
        /* Regra 9 */ ["<COMANDO>", ["<CHAMADA_FUNCAO>"]],
        /* Regra 10 */ ["<COMANDO>", ["<CONTROLE_FLUXO>"]],
        
        // Declarações
        /* Regra 11 */ ["<DECLARACAO>", ["<TIPO>", "ID", "PV"]],
        /* Regra 12 */ ["<DECLARACAO>", ["<TIPO>", "ID", "ATR", "<EXPRESSAO>", "PV"]],
        
        // Tipos
        /* Regra 13 */ ["<TIPO>", ["INT"]],
        /* Regra 14 */ ["<TIPO>", ["CHAR"]],
        /* Regra 15 */ ["<TIPO>", ["FLOAT"]],
        
        // Atribuição
        /* Regra 16 */ ["<ATRIBUICAO>", ["ID", "ATR", "<EXPRESSAO>", "PV"]],
        
        // E/S
        /* Regra 17 */ ["<LEITURA>", ["LEITURA", "ABRE_PAR", "ID", "FECHA_PAR", "PV"]],
        /* Regra 18 */ ["<IMPRESSAO>", ["IMPRESSAO", "ABRE_PAR", "<ARGUMENTO_IMPRESSAO>", "FECHA_PAR", "PV"]],
        /* Regra 19 */ ["<ARGUMENTO_IMPRESSAO>", ["STRING"]],
        /* Regra 20 */ ["<ARGUMENTO_IMPRESSAO>", ["<EXPRESSAO>"]],
        
        // Chamada de função
        /* Regra 21 */ ["<CHAMADA_FUNCAO>", ["ID", "ABRE_PAR", "<LISTA_ARGUMENTOS>", "FECHA_PAR", "PV"]],
        /* Regra 22 */ ["<LISTA_ARGUMENTOS>", ["<EXPRESSAO>", "<LISTA_ARGUMENTOS_REST>"]],
        /* Regra 23 */ ["<LISTA_ARGUMENTOS_REST>", ["VIRGULA", "<EXPRESSAO>", "<LISTA_ARGUMENTOS_REST>"]],
        /* Regra 24 */ ["<LISTA_ARGUMENTOS_REST>", []], // ε
        
        // Controle de fluxo
         /* Regra 25 */ ["<CONTROLE_FLUXO>", ["SE", "ABRE_PAR", "<EXPRESSAO_LOGICA>", "FECHA_PAR", "ABRE_CHAVES", "<LISTA_COMANDOS>", "FECHA_CHAVES"]],
         /* Regra 26 */ ["<CONTROLE_FLUXO>", ["SE", "ABRE_PAR", "<EXPRESSAO_LOGICA>", "FECHA_PAR", "ABRE_CHAVES", "<LISTA_COMANDOS>", "FECHA_CHAVES", "SENAO", "ABRE_CHAVES", "<LISTA_COMANDOS>", "FECHA_CHAVES"]],
        /* Regra 27 */ ["<CONTROLE_FLUXO>", ["ENQUANTO", "ABRE_PAR", "<EXPRESSAO_LOGICA>", "FECHA_PAR", "ABRE_CHAVES", "<LISTA_COMANDOS>", "FECHA_CHAVES"]],
        /* Regra 28 */ ["<CONTROLE_FLUXO>", ["PARA", "ABRE_PAR", "<DECLARACAO>", "<EXPRESSAO_LOGICA>", "PV", "<EXPRESSAO>", "FECHA_PAR", "ABRE_CHAVES", "<LISTA_COMANDOS>", "FECHA_CHAVES"]],
        
        // Expressões
        /* Regra 29 */ ["<EXPRESSAO>", ["<TERMO>", "<EXPRESSAO_REST>"]],
        /* Regra 30 */ ["<EXPRESSAO_REST>", ["MAI", "<TERMO>", "<EXPRESSAO_REST>"]],
        /* Regra 31 */ ["<EXPRESSAO_REST>", ["MEN", "<TERMO>", "<EXPRESSAO_REST>"]],
        /* Regra 32 */ ["<EXPRESSAO_REST>", []], // ε
        
        /* Regra 33 */ ["<TERMO>", ["<FATOR>", "<TERMO_REST>"]],
        /* Regra 34 */ ["<TERMO_REST>", ["MUL", "<FATOR>", "<TERMO_REST>"]],
        /* Regra 35 */ ["<TERMO_REST>", ["DIV", "<FATOR>", "<TERMO_REST>"]],
        /* Regra 36 */ ["<TERMO_REST>", ["MOD", "<FATOR>", "<TERMO_REST>"]],
        /* Regra 37 */ ["<TERMO_REST>", []], // ε
        
        /* Regra 38 */ ["<FATOR>", ["ID"]],
        /* Regra 39 */ ["<FATOR>", ["CONST_INT"]],
        /* Regra 40 */ ["<FATOR>", ["CONST_FLOAT"]],
        /* Regra 41 */ ["<FATOR>", ["ABRE_PAR", "<EXPRESSAO>", "FECHA_PAR"]],
        /* Regra 42 */ ["<FATOR>", ["<CHAMADA_FUNCAO>"]],
        
        // Expressões lógicas
        /* Regra 43 */ ["<EXPRESSAO_LOGICA>", ["<EXPRESSAO>", "<OPERADOR_LOGICO>", "<EXPRESSAO>"]],
        /* Regra 44 */ ["<OPERADOR_LOGICO>", ["COMP"]],
        /* Regra 45 */ ["<OPERADOR_LOGICO>", ["DIF"]],
        /* Regra 46 */ ["<OPERADOR_LOGICO>", ["MENOR"]],
        /* Regra 47 */ ["<OPERADOR_LOGICO>", ["MAIOR"]],
        /* Regra 48 */ ["<OPERADOR_LOGICO>", ["MENOR_IGUAL"]],
        /* Regra 49 */ ["<OPERADOR_LOGICO>", ["MAIOR_IGUAL"]],
        
        // Funções
        /* Regra 50 */ ["<FUNCAO>", ["<TIPO>", "ID", "ABRE_PAR", "<LISTA_PARAMETROS>", "FECHA_PAR", "ABRE_CHAVES", "<LISTA_COMANDOS>", "RETORNO", "<EXPRESSAO>", "PV", "FECHA_CHAVES"]],
        /* Regra 51 */ ["<LISTA_PARAMETROS>", ["<PARAMETRO>", "<LISTA_PARAMETROS_REST>"]],
        /* Regra 52 */ ["<LISTA_PARAMETROS_REST>", ["VIRGULA", "<PARAMETRO>", "<LISTA_PARAMETROS_REST>"]],
        /* Regra 53 */ ["<LISTA_PARAMETROS_REST>", []], // ε
        /* Regra 54 */ ["<PARAMETRO>", ["<TIPO>", "ID"]],
        
        // Retorno
        /* Regra 55 */ ["<RETORNO>", ["RETORNO", "<EXPRESSAO>", "PV"]],
        
        // Regras adicionais para completar 58
        /* Regra 56 */ ["<EXPRESSAO_LOGICA_REST>", ["AND", "<EXPRESSAO_LOGICA>"]],
        /* Regra 57 */ ["<EXPRESSAO_LOGICA_REST>", ["OR", "<EXPRESSAO_LOGICA>"]],
        /* Regra 58 */ ["<EXPRESSAO_LOGICA_REST>", []], // ε
    ];

    private array $tabelaSimbolos = [];
    private array $escopos = ['global'];
    private array $pilha = [];
    private $arvore;

    public function __construct() {
        $this->carregarTabela(TABELA_SINTATICA_JSON);
    }

    private function carregarTabela(string $jsonPath): void {
        try {
            // Verifica existência do arquivo
            if (!file_exists($jsonPath)) {
                $msg = "Arquivo não encontrado: " . json_encode([
                    'caminho' => $jsonPath,
                    'constante' => defined('TABELA_SINTATICA_JSON') ? TABELA_SINTATICA_JSON : 'não definida'
                ]);
                throw new Exception($msg);
            }
    
            // Carrega conteúdo do arquivo
            $json = file_get_contents($jsonPath);
            if ($json === false) {
                throw new Exception("Falha ao ler arquivo: " . $jsonPath);
            }
    
            // Decodifica JSON
            $data = json_decode($json, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $errorMsg = json_last_error_msg();
                throw new Exception("Erro ao decodificar JSON ($errorMsg) em: " . $jsonPath);
            }
    
            // Debug: Log da estrutura carregada
            error_log("DEBUG - Estrutura do JSON carregado: " . print_r(array_keys($data), true));
    
            // Valida estrutura básica
            if (!isset($data['actionTable'], $data['gotoTable'])) {
                $msg = "Estrutura inválida. Chaves encontradas: " . implode(', ', array_keys($data));
                throw new Exception($msg);
            }
    
            // Valida tipos das tabelas
            if (!is_array($data['actionTable']) || !is_array($data['gotoTable'])) {
                $types = [
                    'actionTable' => gettype($data['actionTable']),
                    'gotoTable' => gettype($data['gotoTable'])
                ];
                throw new Exception("Tipos inválidos nas tabelas: " . print_r($types, true));
            }
    
            // Validação adicional das entradas
            $this->validarTabelaAction($data['actionTable']);
            $this->validarTabelaGoto($data['gotoTable']);
    
            // Atribui às propriedades
            $this->actionTable = $data['actionTable'];
            $this->gotoTable = $data['gotoTable'];
    
            error_log("DEBUG - Tabelas carregadas com sucesso!");
            
        } catch (Exception $e) {
            error_log("ERRO CRÍTICO Parser: " . $e->getMessage());
            throw new Exception("Falha ao carregar tabela sintática: " . $e->getMessage());
        }
    }

    private function validarTabelaAction(array $actionTable): void {
        foreach ($actionTable as $state => $transitions) {
            if (!is_numeric($state)) {
                throw new Exception("Estado inválido na actionTable: " . gettype($state));
            }
            
            foreach ($transitions as $token => $action) {
                if (!isset($action['type']) || !in_array($action['type'], ['SHIFT', 'REDUCE', 'ACCEPT'])) {
                    throw new Exception("Ação inválida para token $token no estado $state");
                }
            }
        }
    }
    
    private function validarTabelaGoto(array $gotoTable): void {
        foreach ($gotoTable as $state => $gotos) {
            if (!is_numeric($state)) {
                throw new Exception("Estado inválido na gotoTable: " . gettype($state));
            }
            
            foreach ($gotos as $symbol => $targetState) {
                if (!is_string($symbol) || !str_starts_with($symbol, '<')) {
                    throw new Exception("Símbolo não terminal inválido: $symbol");
                }
            }
        }
    }

    private function adicionarTabelaSimbolos(Token $token, string $tipo, string $categoria): void {
        $this->tabelaSimbolos[] = [
            'nome' => $token->getLexeme(),
            'tipo' => $tipo,
            'especificacao' => $categoria,
            'escopo' => end($this->escopos),
            'linha' => $token->getLine()
        ];
    }

    private function entrarEscopo(string $novoEscopo): void {
        $this->escopos[] = $novoEscopo;
    }

    private function sairEscopo(): void {
        if (count($this->escopos) > 1) {
            array_pop($this->escopos);
        }
    }

    public function analisar(array $tokens): object {
        $this->pilha = ['0']; // Alterado para string
        $indice = 0;
        $arvoresParciais = [];
        $this->escopos = ['global'];

        while (true) {
            $estadoAtual = (string) end($this->pilha); // Garante string
            $tokenAtual = $tokens[$indice] ?? new Token('$', '$', 0, 0);
            $nomeToken = $tokenAtual->getName();

            if (!isset($this->actionTable[$estadoAtual][$nomeToken])) {
                $this->erroSintatico($tokenAtual);
            }

            $acao = $this->actionTable[$estadoAtual][$nomeToken];

            if ($acao['type'] === 'SHIFT') {
                $this->pilha[] = (string) $acao['state']; // Conversão para string
                $arvoresParciais[] = $this->criarNoToken($tokenAtual);
                
                $this->gerenciarEscopo($tokenAtual, $tokens, $indice);
                $indice++;
                
            } elseif ($acao['type'] === 'REDUCE') {
                $regra = $this->producoes[$acao['rule']];
                $numElementos = count($regra[1]);
                
                $no = $this->processarReducao($regra, $numElementos, $arvoresParciais, $tokenAtual);
                $this->aplicarGoto($regra[0], $no, $arvoresParciais);

            } elseif ($acao['type'] === 'ACCEPT') {
                $this->arvore = end($arvoresParciais);
                return $this->arvore;
            }
        }
    }

    private function gerenciarEscopo(Token $token, array $tokens, int $indice): void {
        $nomeToken = $token->getName();
        
        if ($nomeToken === 'ABRE_CHAVES') {
            $this->entrarEscopo('bloco_' . uniqid());
        } elseif ($nomeToken === 'FECHA_CHAVES') {
            $this->sairEscopo();
        } elseif ($nomeToken === 'ID' && isset($tokens[$indice + 1]) && $tokens[$indice + 1]->getName() === 'ABRE_PAR') {
            $this->entrarEscopo($token->getLexeme());
        }
    }

    private function processarReducao(array $regra, int $numElementos, array &$arvoresParciais, Token $token): object {
        $no = (object)[
            'tipo' => $regra[0],
            'filhos' => [],
            'valor' => null,
            'linha' => $token->getLine()
        ];

        if ($numElementos > 0) {
            $no->filhos = array_splice($arvoresParciais, -$numElementos, $numElementos);
            $no->filhos = array_reverse($no->filhos);
            array_splice($this->pilha, -$numElementos, $numElementos);
        }

        $this->tratarReducoesEspecificas($regra, $no);
        return $no;
    }

    private function aplicarGoto(string $naoTerminal, object $no, array &$arvoresParciais): void {
        $estadoTopo = (string) end($this->pilha); // Garante string
        
        if (!isset($this->gotoTable[$estadoTopo][$naoTerminal])) {
            throw new Exception("Erro GOTO para $naoTerminal no estado $estadoTopo");
        }

        $this->pilha[] = (string) $this->gotoTable[$estadoTopo][$naoTerminal]; // Conversão para string
        $arvoresParciais[] = $no;
    }
    private function tratarReducoesEspecificas(array $regra, object $no): void {
        switch ($regra[0]) {
            case '<DECLARACAO>':
                if (count($no->filhos) >= 2) {
                    $tipo = $no->filhos[0]->valor ?? '';
                    $id = $no->filhos[1]->valor ?? '';
                    $this->adicionarTabelaSimbolos(
                        new Token('ID', $id, $no->linha, 0),
                        $tipo,
                        'variável'
                    );
                }
                break;
                
            case '<FUNCAO>':
                if (count($no->filhos) >= 2) {
                    $tipoRetorno = $no->filhos[0]->valor ?? '';
                    $nomeFuncao = $no->filhos[1]->valor ?? '';
                    $this->adicionarTabelaSimbolos(
                        new Token('ID', $nomeFuncao, $no->linha, 0),
                        $tipoRetorno,
                        'função'
                    );
                }
                break;
        }
    }

    private function criarNoToken(Token $token): object {
        return (object)[
            'tipo' => $token->getName(),
            'valor' => $token->getLexeme(),
            'linha' => $token->getLine(),
            'filhos' => []
        ];
    }

    private function erroSintatico(Token $token): void {
        $estadoAtual = end($this->pilha);
        $esperados = implode(', ', array_keys($this->actionTable[$estadoAtual]));
        
        throw new Exception(
            "Erro sintático na linha {$token->getLine()}\n" .
            "Token recebido: {$token->getName()} ('{$token->getLexeme()}')\n" .
            "Estados possíveis: " . ($esperados ?: 'Nenhuma transição válida')
        );
    }

    public function getArvore(): object {
        return $this->arvore;
    }

    public function getTabelaSimbolos(): array {
        return $this->tabelaSimbolos;
    }
}