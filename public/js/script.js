let selected = null;
const messageDiv = document.getElementById('message');

// Função para limpar tudo
function clearHighlights() {
    document.querySelectorAll('.square').forEach(sq => {
        sq.classList.remove('selected', 'valid-move');
    });
}

document.querySelectorAll('.square').forEach(square => {
    square.addEventListener('click', () => {
        const piece = square.querySelector('.piece');
        
        // 1. PEGAR O TURNO (Mais seguro agora)
        const turnText = document.getElementById('turn-text').textContent.toLowerCase();
        const currentTurn = (turnText.includes('white') || turnText.includes('branc')) ? 'white' : 'black';

        // 2. IDENTIFICAR A PEÇA CLICADA
        let pieceColor = null;
        if (piece) {
            pieceColor = piece.classList.contains('white') ? 'white' : 'black';
        }

        const isMyPiece = pieceColor === currentTurn;

        // --- LÓGICA DE SELEÇÃO (INSTANTÂNEA) ---
        if (isMyPiece) {
            console.log("Selecionando peça:", pieceColor);
            clearHighlights();
            selected = square;
            square.classList.add('selected'); // Fica amarelo NA HORA

            // Só depois chamamos os movimentos válidos (em segundo plano)
            fetchValidMoves(square);
            return;
        }

        // --- LÓGICA DE MOVIMENTO ---
        if (selected) {
            // Se clicar na mesma casa, desmarca
            if (square === selected) {
                clearHighlights();
                selected = null;
                return;
            }

            const moveData = {
                from_row: selected.dataset.row,
                from_col: selected.dataset.col,
                to_row: square.dataset.row,
                to_col: square.dataset.col
            };

            console.log("Tentando mover para:", moveData.to_row, moveData.to_col);

            fetch('/chess/move', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(moveData)
            })
            .then(async response => {
                const data = await response.json();
                if (!response.ok) {
                    messageDiv.textContent = data.message || data.error;
                    setTimeout(() => messageDiv.textContent = '', 3000);
                    clearHighlights();
                    selected = null;
                } else {
                    if (data.game_over) {
                        messageDiv.innerHTML = `<strong>${data.message}</strong>`;
                        messageDiv.classList.add('victory-msg');
                        document.querySelector('.board').style.pointerEvents = 'none';
                        return;
                    }
                    window.location.reload();
                }
            })
            .catch(err => console.error("Erro no movimento:", err));
        }
    });
});

function fetchValidMoves(square) {
    fetch('/chess/valid-moves', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ row: square.dataset.row, col: square.dataset.col })
    })
    .then(response => response.json())
    .then(moves => {
        const board = document.querySelector('.board');
        moves.forEach(move => {
            const target = board.querySelector(`[data-row="${move.row}"][data-col="${move.col}"]`);
            if (target) target.classList.add('valid-move');
        });
    })
    .catch(err => console.error("Erro ao buscar movimentos:", err));
}

// Botão Reset
document.getElementById('reset-btn').addEventListener('click', () => {
    fetch('/chess/reset', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
    }).then(() => window.location.reload());
});