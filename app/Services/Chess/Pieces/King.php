<?php

namespace App\Services\Chess\Pieces;

class King extends Piece
{
    public function canMove(array $board, int $fromRow, int $fromCol, int $toRow, int $toCol): bool
    {
        // vai calcular a diferença absoluta entre as linhas e colunas
        $rowDiff = abs($fromRow - $toRow);
        $colDiff = abs($fromCol - $toCol);

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
