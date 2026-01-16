<?php

namespace App\Services\Chess\Pieces;

class Bishop extends Piece
{
    public function __construct(string $color)
    {
        // Chama o construtor do pai passando a cor e o tipo "bishop"
        parent::__construct($color, 'bishop');
    }

    public function canMove(array $board, int $fromRow, int $fromCol, int $toRow, int $toCol): bool
    {
        if ($this->isNullMove($fromRow, $fromCol, $toRow, $toCol)) {
            return false;
        }

        if (abs($fromRow - $toRow) !== abs($fromCol - $toCol)) {
            return false;
        }

        $stepRow = $toRow > $fromRow ? 1 : -1;
        $stepCol = $toCol > $fromCol ? 1 : -1;

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
