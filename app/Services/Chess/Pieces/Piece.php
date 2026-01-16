<?php

// A peça de xadrez
namespace App\Services\Chess\Pieces;

abstract class Piece
{
    public string $color; // white or black
    // Inicializar com string vazia evita o erro de "uninitialized"
    public string $type = ''; // pawn, rook, knight, bishop, queen, king

    public function __construct(string $color, string $type)
    {
        $this->color = $color;
        $this->type = $type;
    }

    // esse ? antes do Piece indica que o parametro pode ser nulo
    public function isSameColor(?Piece $target): bool
    {
        return $target && $target->color === $this->color;
    }

    public function isNullMove(int $fromCol, int $fromRow, int $toCol, int $toRow): bool
    {
        return $fromCol === $toCol && $fromRow === $toRow;
    }

    abstract public function canMove(array $board, int $fromRow, int $fromCol, int $toRow, int $toCol): bool;
}
