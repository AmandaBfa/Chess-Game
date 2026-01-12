<?php

namespace App\Services\Chess;

class ChessGame
{
    public Board $board;  // pawn, rook, knight, bishop, queen, king
    public string $turn = 'white';

    public function __construct()
    {
        $this->board = new Board();
    }

    public function move(int $fromRow, int $fromCol, int $toRow, int $toCol): bool
    {
        $piece = $this->board->squares[$fromRow][$fromCol];

        if (!$piece && $piece->color === $this->turn) {
            return false;
        }

        // validação dos movimentos sera feita depois

        $this->board->squares[$toRow][$toCol] = $piece;
        $this->board->squares[$fromRow][$fromCol] = null;

        $this->turn = $this->turn === 'white' ? 'black' : 'white';

        return true;
    }
}
