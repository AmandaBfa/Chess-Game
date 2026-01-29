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
    public bool $isFinished = false;
    public ?array $lastMove = null;

    public function __construct()
    {
        $this->board = new Board();
        $this->turn = 'white';
    }

    public function move(int $fromRow, int $fromCol, int $toRow, int $toCol): array
    {
        // trava de segurança 
        if ($this->isFinished) {
            return ['success' => false, 'message' => 'A partida acabou. Clique em "Novo Jogo" para reiniciar.'];
        }

        $piece = $this->board->squares[$fromRow][$fromCol] ?? null;
        $target = $this->board->squares[$toRow][$toCol] ?? null;

        // Validações de acesso
        if (!$piece) return ['success' => false, 'message' => 'Posição vazia.'];
        if ($piece->color !== $this->turn) return ['success' => false, 'message' => 'Não é sua vez.'];

        // Exceção: Roque
        if ($piece->type === 'king' && abs($fromCol - $toCol) === 2) {
            return $this->handleCastling($fromRow, $fromCol, $toRow, $toCol); // trata o roque
        }

        // Validações Fisicas de Movimento
        if (!$piece->canMove($this->board->squares, $fromRow, $fromCol, $toRow, $toCol)) {
            return ['success' => false, 'message' => 'Movimento inválido.'];
        }
        if ($this->isMoveIllegal($fromRow, $fromCol, $toRow, $toCol)) {
            return ['success' => false, 'message' => 'Movimento inválido: seu rei ficaria em xeque.'];
        }

        // Registro de captura
        $gameOver = false;
        $winner = null;
        if ($target) {
            $this->registrarCaptura($target);
            if ($target->type === 'king') {
                $gameOver = true;
                $this->isFinished = true;
                $winner = ($piece->color === 'white') ? 'Branco' : 'Preto';
            }
        }

        // Execução Fisica e Atualização do Estado
        $this->board->squares[$toRow][$toCol] = $piece;
        $this->board->squares[$fromRow][$fromCol] = null;
        $piece->hasMoved = true; // marca que a peça já se moveu  

        // Registro no Histórico
        $pecaNome = ucfirst($piece->type);
        $corNome = ($this->turn === 'white') ? 'Branco' : 'Preto';
        $this->moveHistory[] = "{$corNome} {$pecaNome}: ({$fromRow},{$fromCol}) -> ({$toRow},{$toCol})";

        // Regra Especiais
        $this->processSpecialRules($piece, $fromRow, $fromCol, $toRow, $toCol, $target);

        // Verificação de Fim de Jogo
        if ($gameOver) {
            return ['success' => true, 'message' => "Jogo terminado! O vencedor é o jogador $winner.", 'game_over' => true, 'winner' => $winner];
        }

        // Armazena o último movimento
        $this->lastMove = [
            'fromCol' => $fromCol,
            'toCol' => $toCol,
            'fromRow' => $fromRow,
            'toRow' => $toRow,
            'type' => $piece->type,
            'color' => $piece->color
        ];

        // Troca de turno
        $this->turn = ($this->turn === 'white') ? 'black' : 'white';

        // verifica se é mate
        if ($this->isCheckMate($this->turn)) {
            $this->isFinished = true;
            $vencedor = ($this->turn === 'white') ? 'Preto' : 'Branco';
            return [
                'success' => true,
                'message' => "XEQUE-MATE! O vencedor é o jogador $vencedor.",
                'game_over' => true,
                'winner' => $vencedor
            ];
        }

        // verifica se esta em xeque apos o movimento
        $inCheck = $this->isInCheck($this->turn);
        $mensagemFinal = $inCheck ? "XEQUE no Rei " . ($this->turn === 'white' ? 'Branco!' : 'Preto!') : 'Movimento realizado com sucesso!';
        if ($inCheck) {
            $this->moveHistory[] = "Xeque para " . (($this->turn === 'white') ? 'Branco' : 'Preto') . "!";
        }
        return ['success' => true, 'message' => $mensagemFinal, 'check' => $inCheck]; // envia o bool para o js
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
                if ($piece->canMove($this->board->squares, $row, $col, $toRow, $toCol) && !$this->isMoveIllegal($row, $col, $toRow, $toCol)) {
                    $validMoves[] = ['row' => $toRow, 'col' => $toCol]; // se for true, guarda a posição
                }
            }
        }

        return $validMoves;
    }

    // função para encontrar a posição do rei de uma cor específica 
    private function findKing(string $color): ?array
    {
        foreach ($this->board->squares as $rowIndex => $row) {
            foreach ($row as $colIndex => $piece) {
                // verificamos se existe uma peça, se é um rei e se é da cor procurada
                if ($piece && $piece->type === 'king' && $piece->color === $color) {
                    return ['row' => $rowIndex, 'col' => $colIndex];
                }
            }
        }

        // Retorna null se nenhum rei da cor especificada for encontrado
        return null;
    }

    public function isInCheck(string $color): bool
    {
        // Localiza o Rei (usando o camelCase correto)
        $kingPosition = $this->findKing($color);

        if (!$kingPosition) {
            return false; // Rei não encontrado, não pode estar em xeque
        }

        // Verifica todas as peças do tabuleiro
        // foreach ($this->board->squares as $rowIndex => $row) {
        //     foreach ($row as $colIndex => $piece) {
        //         // Se a peça existir e for da cor oposta
        //         if ($piece && $piece->color !== $color) {
        //             // Verifica se essa peça pode mover para a posição do rei
        //             if ($piece->canMove($this->board->squares, $rowIndex, $colIndex, $kingPosition['row'], $kingPosition['col'])) {
        //                 return true; // O rei está em xeque
        //             }
        //         }
        //     }
        // }
        // return false; // O rei não está em xeque

        return $this->isSquareAttacked($kingPosition['row'], $kingPosition['col'], $color);
    }

    public function isSquareAttacked(int $row, int $col, string $color): bool
    {
        foreach ($this->board->squares as $rowIndex => $rowPieces) {
            foreach ($rowPieces as $colIndex => $piece) {
                // Se a peça existir e for da cor oposta
                if ($piece && $piece->color !== $color) {
                    // Verifica se essa peça pode mover para a posição especificada
                    if ($piece->canMove($this->board->squares, $rowIndex, $colIndex, $row, $col)) {
                        return true; // A casa está sendo atacada
                    }
                }
            }
        }

        return false; // A casa não está sendo atacada
    }

    public function isMoveIllegal(int $fromRow, int $fromCol, int $toRow, int $toCol): bool
    {
        // guarda o estado atual do tabuleiro
        $movingPiece = $this->board->squares[$fromRow][$fromCol];
        $targetPiece = $this->board->squares[$toRow][$toCol];

        // executa o movimento temporariamente
        $this->board->squares[$toRow][$toCol] = $movingPiece;
        $this->board->squares[$fromRow][$fromCol] = null;

        // verifica se o rei do jogador que está movendo está em xeque
        $myColor = $movingPiece->color;
        $isStillInCheck = $this->isInCheck($myColor);

        // desfaz o movimento temporário
        $this->board->squares[$fromRow][$fromCol] = $movingPiece;
        $this->board->squares[$toRow][$toCol] = $targetPiece;

        return $isStillInCheck;
    }

    public function isCheckMate(string $color): bool
    {
        // Verifica se o rei está em xeque
        if (!$this->isInCheck($color)) {
            return false; // Não é xeque-mate se o rei não estiver em xeque
        }

        // Verifica todas as peças do jogador
        foreach ($this->board->squares as $rowIndex => $row) {
            foreach ($row as $colIndex => $piece) {
                // Se a peça existir e for da cor do jogador
                if ($piece && $piece->color === $color) {
                    // Verifica todos os movimentos possíveis para essa peça
                    for ($toRow = 0; $toRow < 8; $toRow++) {
                        for ($toCol = 0; $toCol < 8; $toCol++) {
                            if (
                                $piece->canMove($this->board->squares, $rowIndex, $colIndex, $toRow, $toCol) &&
                                !$this->isMoveIllegal($rowIndex, $colIndex, $toRow, $toCol)
                            ) {

                                return false;
                            }
                        }
                    }
                }
            }
        }

        // Se nenhum movimento puder tirar o rei do xeque, é xeque-mate
        return true;
    }

    public function handleCastling(int $fromRow, int $fromCol, int $toRow, int $toCol): array
    {
        $king = $this->board->squares[$fromRow][$fromCol];

        // 1. Verifica se o Rei e a Torre alvo já se moveram ($hasMoved)            
        if ($king->hasMoved) {
            return ['success' => false, 'message' => 'Roque inválido: o Rei já se moveu.'];
        }

        $rookCol = ($toCol === 6) ? 7 : 0; // 7 para o roque pequeno e 0 para o roque grande
        $rook = $this->board->squares[$fromRow][$rookCol];

        if (!$rook || $rook->type != 'rook' || $rook->hasMoved) {
            return ['success' => false, 'message' => 'Roque inválido: a Torre já se moveu ou não existe.'];
        }

        // 2. Verifica se o caminho está livre (squares == null)
        $step = ($toCol === 6) ? 1 : -1;
        for ($col = $fromCol + $step; $col != $rookCol; $col += $step) {
            if ($this->board->squares[$fromRow][$col] !== null) {
                return ['success' => false, 'message' => 'Caminho não está livre para o Roque.'];
            }
        }

        // 3. Verifica se o Rei não está ou passa por Xeque (isInCheck)
        if ($this->isInCheck($this->turn)) {
            return ['success' => false, 'message' => 'Não é permitido fazer Roque em xeque.'];
        }

        $intermediateCol = $fromCol + $step;
        if ($this->isSquareAttacked($fromRow, $intermediateCol, $this->turn)) {
            return ['success' => false, 'message' => 'O Rei não pode passar por uma casa atacada durante o Roque.'];
        }

        // 4. Executa o Roque
        $this->board->squares[$toRow][$toCol] = $king; // move o rei
        $this->board->squares[$fromRow][$fromCol] = null;
        $king->hasMoved = true;

        // move a torre
        $newRookCol = ($toCol === 6) ? 5 : 3;
        $this->board->squares[$fromRow][$newRookCol] = $rook;
        $this->board->squares[$fromRow][$rookCol] = null;
        $rook->hasMoved = true;

        // 4. retorna sucesso
        $howPlayed = ($this->turn === 'white') ? 'Branco' : 'Preto';
        $this->turn = ($this->turn === 'white') ? 'black' : 'white';
        $this->moveHistory[] = "Roque " . ($toCol === 6 ? 'Pequeno' : 'Grande') . " realizado pelo jogador " . $howPlayed;

        return ['success' => true, 'message' => 'Roque realizado com sucesso!', 'check' => $this->isInCheck($this->turn)];
    }

    public function registrarCaptura($target): void
    {
        if ($target->color === 'white') {
            $this->capturedWhite[] = $target->type;
        } else {
            $this->capturedBlack[] = $target->type;
        }
    }

    private function processSpecialRules($piece, $fromRow, $fromCol, $toRow, $toCol, $target): void
    {
        // Implementar regras especiais como En Passant e Promoção de Peão
        if ($piece->type !== 'pawn') {
            return;
        }

        // En Passant
        if ($fromCol !== $toCol && $target === null) {
            $peaoCapturado = $this->board->squares[$fromRow][$toCol]; // Pega a peça ao lado
            $this->registrarCaptura($peaoCapturado);
            $this->board->squares[$fromRow][$toCol] = null;
            $this->moveHistory[count($this->moveHistory) - 1] .= " (En Passant!)";
        }

        // Promoção de Peão
        if ($piece->color === 'white' && $toRow === 0 || $piece->color === 'black' && $toRow === 7) {
            $this->board->squares[$toRow][$toCol] = new \App\Services\Chess\Pieces\Queen($piece->color);
            $this->moveHistory[count($this->moveHistory) - 1] .= " (Promovido a Rainha " . ($piece->color === 'white' ? 'Branca' : 'Preta') . "!)";
        }
    }
}
