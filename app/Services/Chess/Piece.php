<?php

// A peça de xadrez
namespace App\Services\Chess;

class Piece
{
    public string $type;  // pawn, rook, knight, bishop, queen, king
    public string $color; // white or black

    public function __construct(string $type, string $color)
    {
        $this->type = $type;
        $this->color = $color;
    }
}
