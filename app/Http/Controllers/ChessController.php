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
        ]);
    }

    public function move(Request $request)
    {
        $game = session('game');

        if (!$game) {
            return response()->json(['error' => 'Game not found'], 400);
        }

        $result = $game->move(
            (int) $request->from_row,
            (int) $request->from_col,
            (int) $request->to_row,
            (int) $request->to_col
        );

        session(['game' => $game]);

        if (!$result['success']) {
            return response()->json([
                'error' => $result['message']
            ], 400);
        }

        return response()->json([
            'status' => 'success',
            'message' => $result['message'],
        ]);
    }
}
