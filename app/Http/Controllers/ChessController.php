<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Chess\ChessGame;
use Illuminate\Support\Facades\Log;

class ChessController extends Controller
{
    public function index()
    {
        // pega o jogo da session OU cria um novo
        $game = session('game');

        if (!$game) {
            $game = new ChessGame();
            session(['game' => $game]);
        }

        return view('chess', [
            'board' => $game->board->squares,
            'turn' => $game->turn,
            'capturedWhite' => $game->capturedWhite,
            'capturedBlack' => $game->capturedBlack,
            'moveHistory' => $game->moveHistory,
            'isFinished' => $game->isFinished,
        ]);
    }

    public function move(Request $request)
    {
        $game = session('game');

        if (!$game) {
            return response()->json(['error' => 'Game not found'], 400);
        }

        $result = $game->move(
            (int) $request->fromRow,
            (int) $request->fromCol,
            (int) $request->toRow,
            (int) $request->toCol
        );

        session(['game' => $game]);

        if (!$result['success']) {
            return response()->json([
                'error' => $result['message']
            ], 400);
        }

        return response()->json($result);
    }

    public function reset()
    {
        session()->forget('game');
        return response()->json(['success' => true]);
    }

    // Função para obter os movimentos válidos de uma peça -- vai mostrar os quadrados possiveis
    public function getValidMoves(Request $request)
    {
        $game = session('game');

        if (!$game) {
            $game = new ChessGame();
            session(['game' => $game]);
        }

        // PEGA as coordenadas que vem da requisição
        $row = (int) $request->row;
        $col = (int) $request->col;

        // PROCESSA os movimentos validos do jogo
        $validMoves = $game->getValidMoves($row, $col);

        // RETORNA o JSON
        return response()->json($validMoves);
    }
}
