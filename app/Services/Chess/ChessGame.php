<?php

namespace App\Services\Chess;

use App\Services\Chess\Board;

class ChessGame
{
    public Board $board;  // pawn, rook, knight, bishop, queen, king
    public string $turn;
    public array $capturedWhite = [];
    public array $capturedBlack = [];

    public function __construct()
    {
        $this->board = new Board();
        $this->turn = 'white';
    }

    public function move(int $fromRow, int $fromCol, int $toRow, int $toCol): array
    {
        $piece = $this->board->squares[$fromRow][$fromCol] ?? null;
        $target = $this->board->squares[$toRow][$toCol] ?? null;

        if (!$piece) {
            return ['success' => false, 'message' => 'Não existe nenhuma peça nessa posição.'];
        } // evita erro de tentar mover uma peça que nao existe

        if ($piece->color !== $this->turn) {
            $cor = $this->turn === 'white' ? 'Branca' : 'Preta';
            return ['success' => false, 'message' => "É a vez da peça $cor jogar."];
        } // evita que jogue fora da vez

        if (!$piece->canMove(
            $this->board->squares,
            $fromRow,
            $fromCol,
            $toRow,
            $toCol
        )) {
            return ['success' => false, 'message' => 'Movimento inválido para essa peça.'];
        } // valida se a peça pode se mover daquela forma

        if ($target) {
            if ($target->color === 'white') {
                $this->capturedWhite[] = $target->type;
            } else {
                $this->capturedBlack[] = $target->type;
            }
        } // captura a peça se houver uma peça inimiga no destino

        // logica da vitoria
        $gameOver = false;
        $winner = null;

        if ($target && $target->type === 'king') {
            $gameOver = true;
            $winner = $piece->color === 'white' ? 'Branco' : 'Preto';
        }

        // validação dos movimentos sera feita depois

        $this->board->squares[$toRow][$toCol] = $piece;
        $this->board->squares[$fromRow][$fromCol] = null;

        // logica de promoção
        if ($piece->type === 'pawn') {
            // se for branco e chegar na ultima linha e se for preto e chegar na primeira linha
            if (($piece->color === 'white' && $toRow === 0) || ($piece->color === 'black' && $toRow === 7)) {
                // promove para rainha
                $this->board->squares[$toRow][$toCol] = new \App\Services\Chess\Pieces\Queen($piece->color);
            }
        }

        // logica de fim de jogo
        if ($gameOver) {
            return [
                'success' => true,
                'message' => "Jogo terminado! O vencedor é o jogador $winner.",
                'game_over' => true,
                'winner' => $winner
            ];
        }

        // vai trocar o turno se o jogo nao tiver acabado
        $this->turn = $this->turn === 'white' ? 'black' : 'white'; // troca o turno

        return ['success' => true, 'message' => 'Movimento realizado com sucesso!'];
    }

    // funcao para obter os movimentos validos de uma peça -- vai mostrar os quadrados possiveis
    public function getValidMoves(int $row, int $col): array
    {
        $piece = $this->board->squares[$row][$col] ?? null; // para localizar a peça na posição

        if (!$piece) {
            // validação: se nao tiver peça na posição, retorna array vazio
            return [];
        }

        $validMoves = []; // array para armazenar os movimentos válidos

        for ($toRow = 0; $toRow < 8; $toRow++) {
            for ($toCol = 0; $toCol < 8; $toCol++) {
                // chama o metodo que criou em cada peça. passa pelo tabuleiro, posição inicial e final
                if ($piece->canMove($this->board->squares, $row, $col, $toRow, $toCol)) {
                    $validMoves[] = ['row' => $toRow, 'col' => $toCol]; // se for true, guarda a posição
                }
            }
        }

        return $validMoves;
    }
}
