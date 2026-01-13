<?php

namespace App\Services\Chess\Pieces;

class Knight extends Piece
{
    public function __construct(string $color)
    {
        // Chama o construtor do pai passando a cor e o tipo "knight"
        parent::__construct($color, 'knight');
    }

    public function canMove(array $board, int $fromRow, int $fromCol, int $toRow, int $toCol): bool
    {
        // vai calcular a diferença absoluta entre as linhas e colunas
        $rowDiff = abs($fromRow - $toRow);
        $colDiff = abs($fromCol - $toCol);

        // validação do "L" do cavalo, se a diferença formam um "L" (2x1 ou 1x2), retorna false
        if (!($rowDiff === 2 && $colDiff === 1) && !($rowDiff === 1 && $colDiff === 2)) {
            return false;
        }

        // como o cavalo pode pular sobre outras peças, não precisamos verificar o caminho, so verificar o destino

        // verifica a cor do destino, se for a mesma cor, nao pode mover
        $target = $board[$toRow][$toCol] ?? null; // target = alvo

        if ($target && $target->color === $this->color) {
            return false;
        }

        return true;
    }
}
