<?php

namespace App\Services\Chess;

use App\Services\Chess\Board;

class ChessGame
{
    // aqui dentro é onde vai ter a lógica do jogo, feita pelo php

    public Board $board;  // pawn, rook, knight, bishop, queen, king
    public string $turn;
    public array $capturedWhite = [];
    public array $capturedBlack = [];
    public array $moveHistory = [];
    public bool $isFinished = false; // indica se o jogo acabou

    public function __construct()
    {
        $this->board = new Board();
        $this->turn = 'white';
    }

    public function move(int $fromRow, int $fromCol, int $toRow, int $toCol): array
    {
        // trava de segurança -- se o jogo já acabou, não deixa mover mais
        if ($this->isFinished) {
            return [
                'success' => false,
                'message' => 'A partida acabou. Clique em "Novo Jogo" para reiniciar.'
            ];
        }

        // Obtém a peça na posição inicial e o alvo na posição final
        $piece = $this->board->squares[$fromRow][$fromCol] ?? null;
        $target = $this->board->squares[$toRow][$toCol] ?? null;

        // 1. Validações Iniciais
        if (!$piece) return ['success' => false, 'message' => 'Posição vazia.'];
        if ($piece->color !== $this->turn) return ['success' => false, 'message' => 'Não é sua vez.'];
        if (!$piece->canMove($this->board->squares, $fromRow, $fromCol, $toRow, $toCol)) {
            return ['success' => false, 'message' => 'Movimento inválido.'];
        }

        // 2. Lógica de Captura e Fim de Jogo (Rei)
        $gameOver = false;
        $winner = null;

        if ($target) {
            // Log de captura
            \Illuminate\Support\Facades\Log::info("Peça capturada: " . $target->type . " Cor: " . $target->color);

            // Registra no cemitério
            if ($target->color === 'white') {
                $this->capturedWhite[] = $target->type;
            } else {
                $this->capturedBlack[] = $target->type;
            }

            // Verifica se o jogo acabou (captura do rei)
            if ($target->type === 'king') {
                $gameOver = true;
                $this->isFinished = true;
                $winner = ($piece->color === 'white') ? 'Branco' : 'Preto';
            }
        }

        // 3. Registro no Histórico
        $corNome = ($this->turn === 'white') ? 'Branco' : 'Preto';
        $pecaNome = ucfirst($piece->type); // ucfirst para deixar a primeira letra maiuscula
        $this->moveHistory[] = "{$corNome} {$pecaNome}: ({$fromRow},{$fromCol}) -> ({$toRow},{$toCol})";

        // 4. Execução do Movimento
        $this->board->squares[$toRow][$toCol] = $piece;
        $this->board->squares[$fromRow][$fromCol] = null;

        // 5. Lógica de Promoção (Peão)
        if ($piece->type === 'pawn') {
            if (($piece->color === 'white' && $toRow === 0) || ($piece->color === 'black' && $toRow === 7)) {
                $this->board->squares[$toRow][$toCol] = new \App\Services\Chess\Pieces\Queen($piece->color);
                $this->moveHistory[count($this->moveHistory) - 1] .= " (Promovido a Rainha!)";
            }
        }

        // 6. Retorno de Vitória ou Troca de Turno
        if ($gameOver) {
            return [
                'success' => true,
                'message' => "Jogo terminado! O vencedor é o jogador $winner.",
                'game_over' => true,
                'winner' => $winner
            ];
        }

        // Troca de turno
        $this->turn = ($this->turn === 'white') ? 'black' : 'white';

        return ['success' => true, 'message' => 'Movimento realizado com sucesso!'];
    }

    // Função para obter os movimentos validos de uma peça -- vai mostrar os quadrados possiveis
    public function getValidMoves(int $row, int $col): array
    {
        // trava de segurança 
        if ($this->isFinished) {
            return [];
        }

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
