<?php

namespace App\Services\Chess\Pieces;

class Pawn extends Piece
{
    public function __construct(string $color)
    {
        // Chama o construtor do pai passando a cor e o tipo "pawn"
        parent::__construct($color, 'pawn');
    }

    public function canMove(array $board, int $fromRow, int $fromCol, int $toRow, int $toCol): bool
    {
        $direction = $this->color === 'white' ? -1 : 1;
        $startRow  = $this->color === 'white' ? 6 : 1;

        $target = $board[$toRow][$toCol] ?? null;

        // andar 1 casa
        if ($fromCol === $toCol && $toRow === $fromRow + $direction && !$target) {
            return true;
        }

        // andar 2 casas no primeiro movimento
        if (
            $fromCol === $toCol &&
            $fromRow === $startRow &&
            $toRow === $fromRow + 2 * $direction &&
            !$target &&
            !$board[$fromRow + $direction][$fromCol]
        ) {
            return true;
        }

        // captura diagonal
        if (
            abs($fromCol - $toCol) === 1 &&
            $toRow === $fromRow + $direction &&
            $target &&
            $target->color !== $this->color
        ) {
            return true;
        }

        return false;
    }
}
