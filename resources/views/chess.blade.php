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
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Chess Master</title>
</head>

<body>
    <div class="game-area">
        <div class="info">
            <span id="turn-text">Turno: {{ ucfirst($turn) }}</span>
            <div id="message"></div>
            <button id="reset-btn" class="btn-reset">Novo Jogo</button>
        </div>

        <div class="board">
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
    </div>
    <script src="{{ asset('js/script.js') }}"></script>
</body>

</html>
