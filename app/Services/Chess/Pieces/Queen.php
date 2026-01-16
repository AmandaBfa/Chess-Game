<?php

namespace App\Services\Chess\Pieces;

class Queen extends Piece
{
    public function __construct(string $color)
    {
        // Chama o construtor do pai passando a cor e o tipo "queen"
        parent::__construct($color, 'queen');
    }

    public function canMove(array $board, int $fromRow, int $fromCol, int $toRow, int $toCol): bool
    {
        if ($this->isNullMove($fromRow, $fromCol, $toRow, $toCol)) {
            return false;
        }

        $isStraight = ($fromRow === $toRow || $fromCol === $toCol);
        $isDiagonal = abs($fromRow - $toRow) === abs($fromCol - $toCol); // regra matemática da diagonal

        if (!$isStraight && !$isDiagonal) {
            return false;
        }

        $stepRow = $toRow > $fromRow ? 1 : ($toRow < $fromRow ? -1 : 0);
        $stepCol = $toCol > $fromCol ? 1 : ($toCol < $fromCol ? -1 : 0);

        $currentRow = $fromRow + $stepRow;
        $currentCol = $fromCol + $stepCol;

        while ($currentRow !== $toRow || $currentCol !== $toCol) {
            if ($board[$currentRow][$currentCol] !== null) {
                return false;
            }
            $currentRow += $stepRow;
            $currentCol += $stepCol;
        }

        $target = $board[$toRow][$toCol] ?? null;
        if ($this->isSameColor($target)) {
            return false;
        }

        return true;
    }
}
