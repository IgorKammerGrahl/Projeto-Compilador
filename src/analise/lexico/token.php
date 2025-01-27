<?php

namespace App\Lexico;
class Token {
    private string $name;
    private string $lexeme;
    private int $inicio;
    private int $line;

    public function __construct(string $name, string $lexeme, int $inicio, int $line) {
        $this->name = $name;
        $this->lexeme = $lexeme;
        $this->inicio = $inicio;
        $this->line = $line;
    }

    public function getName(bool $lower = false): string {
        return $lower ? strtolower($this->name) : $this->name;
    }

    public function getLexeme(): string {
        return $this->lexeme;
    }

    public function getInicio(): int {
        return $this->inicio;
    }

    public function getLine(): int {
        return $this->line;
    }

    public function __toString(): string {
        return "{$this->getName()} - {$this->getLexeme()}";
    }
}
