@php
    $icons = [
        'white' => ['pawn' => '♙', 'rook' => '♖', 'knight' => '♘', 'bishop' => '♗', 'queen' => '♕', 'king' => '♔'],
        'black' => ['pawn' => '♟', 'rook' => '♜', 'knight' => '♞', 'bishop' => '♝', 'queen' => '♛', 'king' => '♚'],
    ];
@endphp


<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    <title>Chess Master</title>
</head>

<body>

    <div class="game-container">
        <div class="graveyard black-graveyard">
            @foreach ($capturedBlack as $type)
                <span class="captured-piece">{{ $icons['black'][$type] }}</span>
            @endforeach
        </div>


        <div class="game-area">
            <div class="info text-center mb-3">
                <span id="turn-text" class="badge bg-secondary p-2">Turno: {{ ucfirst($turn) }}</span>
                <div id="message" class="mt-2"></div>
                <button id="button" onclick="resetGame()" class="btn btn-outline-danger btn-sm mt-2">Novo
                    Jogo</button>
            </div>

            <div class="main-play-container">
                <div class="board {{ $isFinished ? 'frozen' : '' }}">
                    @foreach ($board as $rowIndex => $row)
                        @foreach ($row as $colIndex => $piece)
                            @php $colorClass = ($rowIndex + $colIndex) % 2 === 0 ? 'light' : 'dark'; @endphp
                            <div class="square {{ $colorClass }}" data-row="{{ $rowIndex }}"
                                data-col="{{ $colIndex }}">
                                @if ($piece)
                                    <span class="piece {{ $piece->color }}">
                                        {{ $icons[$piece->color][$piece->type] }}
                                    </span>
                                @endif
                            </div>
                        @endforeach
                    @endforeach
                </div>

                <div class="sidebar">
                    <h5 class="text-center border-bottom pb-2">Histórico</h5>
                    <div class="history-container">
                        @if (empty($moveHistory))
                            <p class="text-muted text-center">Aguardando jogadas...</p>
                        @else
                            <ul class="list-unstyled">
                                @foreach ($moveHistory as $index => $move)
                                    <li class="history-item"><strong>{{ $index + 1 }}.</strong> {{ $move }}
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="graveyard white-graveyard">
            @foreach ($capturedWhite as $type)
                <span class="captured-piece">{{ $icons['white'][$type] }}</span>
            @endforeach
        </div>

    </div>

    <div class="modal fade" id="gameOverModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-dark text-white border-secondary">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title">🏆 Partida Encerrada</h5>
                </div>
                <div class="modal-body text-center">
                    <h2 id="winner-message" class="text-warning"></h2>
                    <p>O Rei foi capturado com sucesso!</p>
                </div>
                <div class="modal-footer border-secondary justify-content-center">
                    <button type="button" class="btn btn-primary" onclick="resetGame()">Novo Jogo</button>

                    <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Analisar
                        Partida</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/script.js') }}"></script>
</body>

</html>
