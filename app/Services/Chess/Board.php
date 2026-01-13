<?php

// O tabuleiro de xadrez é representado por uma matriz 8x8
namespace App\Services\Chess;

use App\Services\Chess\Pieces\King;
use App\Services\Chess\Pieces\Pawn;
use App\Services\Chess\Pieces\Rook;
use App\Services\Chess\Pieces\Queen;
use App\Services\Chess\Pieces\Bishop;
use App\Services\Chess\Pieces\Knight;

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
            $this->squares[1][$i] = new Pawn('black');
            $this->squares[6][$i] = new Pawn('white');
        }

        // torres
        $this->squares[0][0] = new Rook('black');
        $this->squares[0][7] = new Rook('black');
        $this->squares[7][0] = new Rook('white');
        $this->squares[7][7] = new Rook('white');

        // cavalos 
        $this->squares[0][1] = new Knight('black');
        $this->squares[0][6] = new Knight('black');
        $this->squares[7][1] = new Knight('white');
        $this->squares[7][6] = new Knight('white');

        // bispos
        $this->squares[0][2] = new Bishop('black');
        $this->squares[0][5] = new Bishop('black');
        $this->squares[7][2] = new Bishop('white');
        $this->squares[7][5] = new Bishop('white');

        // rainha
        $this->squares[0][3] = new Queen('black');
        $this->squares[7][3] = new Queen('white');

        // rei
        $this->squares[0][4] = new King('black');
        $this->squares[7][4] = new King('white');
    }
}
