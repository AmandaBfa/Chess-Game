<?php

// O tabuleiro de xadrez é representado por uma matriz 8x8
namespace App\Services\Chess;

class Board
{
    public array $squares = [];

    public function __construct()
    {
        $this->initialize();
    }

    private function initialize(): void
    {
        $this->squares = array_fill(0, 8, array_fill(0, 8, null));

        // peôes
        for ($i = 0; $i < 8; $i++) {
            $this->squares[1][$i] = new Piece('pawn', 'black');
            $this->squares[6][$i] = new Piece('pawn', 'white');
        }

        // torres
        $this->squares[0][0] = new Piece('rook', 'black');
        $this->squares[0][7] = new Piece('rook', 'black');
        $this->squares[7][0] = new Piece('rook', 'white');
        $this->squares[7][7] = new Piece('rook', 'white');

        // cavalos 
        $this->squares[0][1] = new Piece('knight', 'black');
        $this->squares[0][6] = new Piece('knight', 'black');
        $this->squares[7][1] = new Piece('knight', 'white');
        $this->squares[7][6] = new Piece('knight', 'white');

        // bispos
        $this->squares[0][2] = new Piece('bishop', 'black');
        $this->squares[0][5] = new Piece('bishop', 'black');
        $this->squares[7][2] = new Piece('bishop', 'white');
        $this->squares[7][5] = new Piece('bishop', 'white');

        // rainha
        $this->squares[0][3] = new Piece('queen', 'black');
        $this->squares[7][3] = new Piece('queen', 'white');

        // rei
        $this->squares[0][4] = new Piece('king', 'black');
        $this->squares[7][4] = new Piece('king', 'white');
    }
}
