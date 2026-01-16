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
}
