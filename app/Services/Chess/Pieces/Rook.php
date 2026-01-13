<?php

namespace App\Services\Chess\Pieces;

class Rook extends Piece
{
    public function __construct(string $color)
    {
        // Chama o construtor do pai passando a cor e o tipo "rook"
        parent::__construct($color, 'rook');
    }

    public function canMove(array $board, int $fromRow, int $fromCol, int $toRow, int $toCol): bool
    {
        // so vai andar linha ou coluna
        if ($fromRow !== $toRow && $fromCol !== $toCol) {
            return false;
        }

        // Esta direcionando o "passo" -- iteração direcional
        $stepRow = $toRow > $fromRow ? 1 : ($toRow < $fromRow ? -1 : 0); // se a linha do destino for maior, passo 1, se for menor, passo -1, se for igual, passo 0
        $stepCol = $toCol > $fromCol ? 1 : ($toCol < $fromCol ? -1 : 0); // se a coluna do destino for maior, passo 1, se for menor, passo -1, se for igual, passo 0

        // iniciando a caminhada -- vai calcular a unidade de passos (do inicio ao destino)
        $currentRow = $fromRow + $stepRow; // primeira linha a ser verificada 
        $currentCol = $fromCol + $stepCol; // primeira coluna a ser verificada

        // laço de repetição, enquanto não chegar no destino continue andando e verificando
        while ($currentRow !== $toRow || $currentCol !== $toCol) {
            if ($board[$currentRow][$currentCol] !== null) {
                return false;
            }
            // de o proximo passo
            $currentRow += $stepRow;
            $currentCol += $stepCol;
        }

        // quando o while terminar, quer dizer que o caminho estava livre, agora verificar o destino(para onde a torre vai ficar)...

        $target = $board[$toRow][$toCol] ?? null;

        // se tiver peça do mesmo cor no destino -- se for a mesma cor, nao pode pular em cima (capturar) e se for diferente, pode capturar
        if ($target && $target->color === $this->color) {
            return false;
        }
        return true;
    }
}
