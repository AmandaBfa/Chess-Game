@php
    $icons = [
        'white' => [
            'pawn' => '♙',
            'rook' => '♖',
            'knight' => '♘',
            'bishop' => '♗',
            'queen' => '♕',
            'king' => '♔',
        ],
        'black' => [
            'pawn' => '♟',
            'rook' => '♜',
            'knight' => '♞',
            'bishop' => '♝',
            'queen' => '♛',
            'king' => '♚',
        ],
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

    <title>Game Chess</title>
</head>

<body>
    <h1 style="color:red">BLADE FUNCIONANDO</h1>


    <div class="board">
        @foreach ($board as $rowIndex => $row)
            @foreach ($row as $colIndex => $piece)
                @php
                    $color = ($rowIndex + $colIndex) % 2 === 0 ? 'white' : 'black';
                @endphp

                <div class="square {{ $color }}" data-row="{{ $rowIndex }}" data-col="{{ $colIndex }}">
                    @if ($piece)
                        {{ $icons[$piece->color][$piece->type] }}
                    @endif
                </div>
            @endforeach
        @endforeach
    </div>

    <script src="{{ asset('js/script.js') }}"></script>
</body>

</html>
