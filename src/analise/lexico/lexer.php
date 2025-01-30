<?php

namespace App\Lexico;

use App\Lexico\Token;
use Exception;

class Lexer {
    private string $entrada;
    private int $posicao;
    private array $tokens;
    private int $estadoAtual;
    private array $tabelaTransicoes;
    private array $erros;

    public function __construct(string $entrada, string $jsonPath) {
        $this->entrada = $entrada;
        $this->posicao = 0;
        $this->tokens = [];
        $this->estadoAtual = 0;
        $this->erros = [];
        $this->carregarTabela($jsonPath);
    }

    private function carregarTabela(string $jsonPath): void {
        if (!file_exists($jsonPath)) {
            throw new Exception("Arquivo JSON não encontrado: {$jsonPath}");
        }

        $json = file_get_contents($jsonPath);
        $this->tabelaTransicoes = json_decode($json, true);
    }

    public function analisar(): array {
        $linha = 1;
        $coluna = 1;
        
        while ($this->posicao < strlen($this->entrada)) {
            $caractere = $this->entrada[$this->posicao];

            if ($caractere === "\n") {
                $linha++;
                $coluna = 1;
                $this->posicao++;
                continue;
            }

            if ($this->processarEspacos($caractere) ||
                $this->processarStrings($linha, $coluna) ||
                $this->processarNumero($linha, $coluna) ||
                $this->processarOperadoresCompostos($linha, $coluna) ||
                $this->processarPalavraReservadaOuIdentificador($linha, $coluna) ||
                $this->processarSimbolosEspeciais($linha, $coluna)) {
                continue;
            }

            $this->erros[] = "Erro Léxico: Caractere inválido '$caractere' na linha $linha, coluna $coluna";
            $this->posicao++;
            $coluna++;
        }

        return ['tokens' => $this->tokens, 'erros' => $this->erros];
    }


    private function processarEspacos(string $caractere): bool {
        if (ctype_space($caractere)) {
            $this->posicao++;
            return true;
        }
        return false;
    }

    private function processarNumero(int &$linha, int &$coluna): bool {
        if (!ctype_digit($this->entrada[$this->posicao])) {
            return false;
        }
        
        $inicioColuna = $coluna;
        $lexema = '';
        $temPonto = false;

        while ($this->posicao < strlen($this->entrada) &&
               (ctype_digit($this->entrada[$this->posicao]) ||
                ($this->entrada[$this->posicao] === '.' && !$temPonto))) {
            if ($this->entrada[$this->posicao] === '.') {
                $temPonto = true;
            }
            $lexema .= $this->entrada[$this->posicao];
            $this->posicao++;
            $coluna++;
        }

        $tipo = $temPonto ? 'CONST_FLOAT' : 'CONST_INT';
        $this->tokens[] = new Token($tipo, $lexema, $linha, $inicioColuna);
        return true;
    }

    private function processarPalavraReservadaOuIdentificador(int &$linha, int &$coluna): bool {
        if (!ctype_alpha($this->entrada[$this->posicao])) {
            return false;
        }
        
        $inicioColuna = $coluna;
        $lexema = '';
        while ($this->posicao < strlen($this->entrada) && ctype_alnum($this->entrada[$this->posicao])) {
            $lexema .= $this->entrada[$this->posicao];
            $this->posicao++;
            $coluna++;
        }
        
        $token = $this->verificarPalavraReservada($lexema);
        $this->tokens[] = new Token($token, $lexema, $linha, $inicioColuna);
        return true;
    }

    private function verificarPalavraReservada(string $lexema): string {
        $palavrasReservadas = [
            'main' => 'MAIN',
            'int' => 'INT',
            'float' => 'FLOAT',
            'char' => 'CHAR',
            'se' => 'SE',          
            'senao' => 'SENAO',   
            'enquanto' => 'ENQUANTO',
            'retorno' => 'RETORNO',
            'escreva' => 'ESCREVA',
            'leia' => 'LEIA'
        ];

        $lexemaLower = strtolower($lexema);
        return $palavrasReservadas[$lexemaLower] ?? 'ID';
    }

    private function processarSimbolosEspeciais(int &$linha, int &$coluna): bool {
        $simbolos = [
            '(' => 'ABRE_PAR',
            ')' => 'FECHA_PAR',
            '{' => 'ABRE_CHAVES',
            '}' => 'FECHA_CHAVES',
            ';' => 'PV',
            '=' => 'ATR',
            ',' => 'VIRGULA',
            '+' => 'MAIS',
            '-' => 'MENOS',
            '*' => 'MULT',
            '/' => 'DIV'
        ];
        
        if (isset($simbolos[$this->entrada[$this->posicao]])) {
            $this->tokens[] = new Token($simbolos[$this->entrada[$this->posicao]], $this->entrada[$this->posicao], $linha, $coluna);
            $this->posicao++;
            $coluna++;
            return true;
        }
        return false;
    }
    private function processarOperadoresCompostos(int &$linha, int &$coluna): bool {
        $caractere = $this->entrada[$this->posicao];
        
        if (in_array($caractere, ['=', '!', '<', '>'], true)) {
            $operadorBuffer = $caractere;
            $proxPosicao = $this->posicao + 1;
            $proxCaractere = $proxPosicao < strlen($this->entrada) ? $this->entrada[$proxPosicao] : '';

            if (in_array($operadorBuffer . $proxCaractere, ['==', '!=', '<=' , '>='], true)) {
                $tokenTipo = match ($operadorBuffer . $proxCaractere) {
                    '==' => 'COMP',
                    '!=' => 'DIF',
                    '<=' => 'MENOR_IGUAL',
                    '>=' => 'MAIOR_IGUAL',
                };

                $this->tokens[] = new Token($tokenTipo, $operadorBuffer . $proxCaractere, $linha, $coluna);
                $this->posicao += 2;
                $coluna += 2; 
                return true;
            } else {
                $tokenTipo = match ($caractere) {
                    '=' => 'ATR',
                    '!' => 'NEGACAO',
                    '<' => 'MENOR',
                    '>' => 'MAIOR',
                    default => 'DESCONHECIDO'
                };

                if ($tokenTipo !== 'DESCONHECIDO') {
                    $this->tokens[] = new Token($tokenTipo, $caractere, $linha, $coluna);
                    $this->posicao++;
                    $coluna++; 
                    return true;
                }
            }
        }
        return false;
    }

    private function processarStrings(int &$linha, int &$coluna): bool {
        if ($this->entrada[$this->posicao] === '"') {
            $inicioColuna = $coluna;
            $lexema = '"';
            $this->posicao++;
            $coluna++;

            while ($this->posicao < strlen($this->entrada) && $this->entrada[$this->posicao] !== '"') {
                $lexema .= $this->entrada[$this->posicao];
                $this->posicao++;
                $coluna++;
            }

            if ($this->posicao < strlen($this->entrada) && $this->entrada[$this->posicao] === '"') {
                $lexema .= '"';
                $this->posicao++;
                $coluna++;
                $this->tokens[] = new Token('STRING', $lexema, $linha, $inicioColuna);
                return true;
            } else {
                $this->erros[] = "Erro Léxico: String não fechada na linha $linha, coluna $coluna";
            }
        }
        return false;
    }

}