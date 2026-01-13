<?php

namespace App\Services\Chess\Pieces;

class Bishop extends Piece
{
    public function canMove(array $board, int $fromRow, int $fromCol, int $toRow, int $toCol): bool
    {
        if ($fromRow === $toRow && $fromCol === $toCol) {
            return false;
        } // so para evitar movimentos nulos

        if (abs($fromRow - $toRow) !== abs($fromCol - $toCol)) {
            return false;
        }

        $stepRow = $toRow > $fromRow ? 1 : -1;
        $stepCol = $toCol > $fromCol ? 1 : -1;

        $currentRow = $fromRow + $stepRow;
        $currentCol = $fromCol + $stepCol;

        while ($currentRow !== $toRow && $currentCol !== $toCol) {
            if ($board[$currentRow][$currentCol] !== null) {
                return false;
            }
            $currentRow += $stepRow;
            $currentCol += $stepCol;
        }

        $target = $board[$toRow][$toCol] ?? null;

        if ($target && $target->color === $this->color) {
            return false;
        }
        return true;
    }
}
