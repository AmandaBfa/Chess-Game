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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <title>Game Chess</title>
</head>

<body>
    <h1 style="color:red">BLADE FUNCIONANDO</h1>
    <h2>Turno: {{ ucfirst($turn) }}</h2>

    <div class="board mt-4">
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
