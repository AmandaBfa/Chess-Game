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

        // Se houver uma mensagem de vitória na tela, não faz nada
        if (document.getElementById('message').classList.contains('victory-msg')) {
            console.log("Tabuleiro congelado para análise.");
            return; 
        }

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
                fromRow: selected.dataset.row,
                fromCol: selected.dataset.col,
                toRow: square.dataset.row,
                toCol: square.dataset.col
            };

            console.log("Tentando mover para:", moveData.toRow, moveData.toCol);

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
                    // se o jogo acabou
                    if (data.game_over) {
                        console.log("VITÓRIA DETECTADA!");
                    
                        // 1. CORREÇÃO: document (com 'd') para congelar o tabuleiro
                        const boardElement = document.querySelector('.board');
                        if (boardElement) boardElement.classList.add('frozen');
                    
                        // 2. MENSAGEM PERSISTENTE: Para aparecer na div fixa enquanto analisa
                        messageDiv.textContent = data.message;
                        messageDiv.classList.add('victory-msg');
                    
                        // 3. MODAL
                        document.getElementById('winner-message').innerText = `Vencedor: ${data.winner}`;
                        const modalElement = document.getElementById('gameOverModal');
                        const gameOverModal = new bootstrap.Modal(modalElement);
                        gameOverModal.show();
                        
                        return; // Para o fluxo aqui para não dar reload
                    }
                    // se houve xeque
                    if (data.check) {
                        messageDiv.textContent = data.message;
                        messageDiv.classList.remove('victory-msg');
                        messageDiv.classList.add('warning-msg');
                        
                        console.log("Alerta de Xeque disparado!");
                
                        setTimeout(() => {
                            window.location.reload();
                        }, 2000); // 2 segundos para o humano conseguir ler
                    } else {
                        // Se não for xeque nem fim de jogo, recarrega na hora
                        window.location.reload();
                    }
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
function resetGame() {
    fetch('/chess/reset', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        }
    })
    .catch(err => console.error("Erro ao resetar:", err));
}

const mainResetBtn = document.getElementById('reset-btn');
if (mainResetBtn) {
    mainResetBtn.addEventListener('click', resetGame);
}