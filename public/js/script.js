let selected = null;
const messageDiv = document.getElementById('message');

// Tempo inicial em segundos 
let timeLeft = {
    white: 600,
    black: 600
};

let timerInterval = null;
let gameStarted = false; // O relógio só começa após o primeiro lance

// Função para limpar tudo
function clearHighlights() {
    document.querySelectorAll('.square').forEach(sq => {
        sq.classList.remove('selected', 'valid-move');
    });
}

updateTimerDisplay();

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
            .then(data => {
                if (data.success) {
                    // 1. Atualiza o turno com o que veio do PHP
                    currentTurn = data.turn; 
        
                    // 2. Inicia o relógio se for o primeiro lance
                    if (!gameStarted) {
                        gameStarted = true;
                        startChessClock();
                    }
        
                    // 3. Atualiza o visual (Glow do jogador ativo)
                    marcarJogadorAtivo(currentTurn);
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

function startChessClock() {
    // Se já houver um intervalo a correr, não criamos outro (evita o relógio acelerar)
    if (timerInterval) return;

    timerInterval = setInterval(() => {
        // 'currentTurn' deve ser a variável que guarda se é 'white' ou 'black'
        if (timeLeft[currentTurn] > 0) {
            timeLeft[currentTurn]--;
            updateTimerDisplay();
        } else {
            // O tempo acabou!
            stopChessClock();
            alert("O tempo acabou! Vitoria das " + (currentTurn === 'white' ? 'Pretas' : 'Brancas'));
        }
    }, 1000);
}

function stopChessClock() {
    clearInterval(timerInterval);
    timerInterval = null;
}

function updateTimerDisplay() {
    const format = (seconds) => {
        const mins = Math.floor(seconds / 60);
        const secs = seconds % 60;
        // padStart(2, '0') garante que 9 segundos fiquem "09"
        return `${mins}:${secs.toString().padStart(2, '0')}`;
    };

    document.getElementById('timer-white').innerText = format(timeLeft.white);
    document.getElementById('timer-black').innerText = format(timeLeft.black);
}

function marcarJogadorAtivo(corAtiva) {
    // Remove o destaque de ambos
    document.querySelector('.bottom-panel').classList.remove('active');
    document.querySelector('.top-panel').classList.remove('active');

    // Adiciona o brilho azul marinho apenas ao jogador da vez
    if (corAtiva === 'white') {
        document.querySelector('.bottom-panel').classList.add('active');
    } else {
        document.querySelector('.top-panel').classList.add('active');
    }
}
