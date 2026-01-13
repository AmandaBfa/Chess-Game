<?php

// A peça de xadrez
namespace App\Services\Chess\Pieces;

abstract class Piece
{
    public string $color; // white or black

    public function __construct(string $color)
    {
        $this->color = $color;
    }

    abstract public function canMove(array $board, int $fromRow, int $fromCol, int $toRow, int $toCol): bool;
}
