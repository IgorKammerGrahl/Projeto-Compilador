<?php

namespace App\Semantico;

use Exception;

class Semantico {
    public function analisar(object $arvore): void {
        $this->processarNodo($arvore);
    }

    private function processarNodo(object $nodo): void {
        switch ($nodo->tipo) {
            case '<DECLARACAO>':
                // Validações de declaração
                break;
            case '<EXPRESSAO>':
                // Validações de expressão
                break;
            default:
                foreach ($nodo->filhos as $filho) {
                    $this->processarNodo($filho);
                }
        }
    }
}


