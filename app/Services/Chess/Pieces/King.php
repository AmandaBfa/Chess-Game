<?php

namespace App\Services\Chess\Pieces;

class King extends Piece
{
    public function __construct(string $color)
    {
        // Chama o construtor do pai passando a cor e o tipo "king"
        parent::__construct($color, 'king');
    }

    public function canMove(array $board, int $fromRow, int $fromCol, int $toRow, int $toCol): bool
    {
        // evita movimentos nulos
        if ($fromRow === $toRow && $fromCol === $toCol) {
            return false;
        }

        // vai calcular a diferença absoluta entre as linhas e colunas
        $rowDiff = abs($fromRow - $toRow);
        $colDiff = abs($fromCol - $toCol);

        // validação do movimento do rei, que pode andar apenas uma casa em qualquer direção
        if (!($rowDiff <= 1 && $colDiff <= 1)) {
            return false;
        }

        // verifica a cor do destino, se for a mesma cor, nao pode mover
        $target = $board[$toRow][$toCol] ?? null;

        if ($target && $target->color === $this->color) {
            return false;
        }

        return true;
    }
}
