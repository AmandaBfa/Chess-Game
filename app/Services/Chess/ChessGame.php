<?php

namespace App\Services\Chess;

use App\Services\Chess\Board;

class ChessGame
{
    public Board $board;  // pawn, rook, knight, bishop, queen, king
    public string $turn;

    public function __construct()
    {
        $this->board = new Board();
        $this->turn = 'white';
    }

    public function move(int $fromRow, int $fromCol, int $toRow, int $toCol): bool
    {
        $piece = $this->board->squares[$fromRow][$fromCol] ?? null;

        if (!$piece) {
            return false;
        }

        if ($piece->color !== $this->turn) {
            return false;
        }

        if (!$piece->canMove(
            $this->board->squares,
            $fromRow,
            $fromCol,
            $toRow,
            $toCol
        )) {
            return false;
        }

        // validação dos movimentos sera feita depois

        $this->board->squares[$toRow][$toCol] = $piece;
        $this->board->squares[$fromRow][$fromCol] = null;

        $this->turn = $this->turn === 'white' ? 'black' : 'white';

        return true;
    }
}
