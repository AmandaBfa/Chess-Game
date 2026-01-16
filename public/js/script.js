let selected = null;

// Pegamos a div de mensagem uma única vez aqui fora
const messageDiv = document.getElementById('message');

document.querySelectorAll('.square').forEach(square => {
    square.addEventListener('click', () => {

        // Caso 1: Nenhuma peça selecionada
        if (!selected) {
            if (square.textContent.trim() === '') return;

            selected = square;
            square.classList.add('selected');
            return;
        }

        // Caso 2: Clicou na mesma casa → desmarca
        if (square === selected) {
            selected.classList.remove('selected');
            selected = null;
            return;
        }

        // Preparação dos dados para o movimento
        const moveData = {
            from_row: selected.dataset.row,
            from_col: selected.dataset.col,
            to_row: square.dataset.row,
            to_col: square.dataset.col
        };

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
                // --- CASO DE ERRO ---
                messageDiv.textContent = data.message || data.error;
                
                setTimeout(() => {
                    messageDiv.textContent = '';
                }, 3000);

                selected.classList.remove('selected');
                selected = null;
            } else {
                // --- CASO DE SUCESSO (Movimento Realizado) ---

                // 1. Verificamos se o servidor avisou que o jogo ACABOU
                if (data.game_over) {
                    messageDiv.innerHTML = `<strong>${data.message}</strong>`;
                    messageDiv.classList.add('victory-msg'); // Aplica o estilo de vitória
                    
                    // Trava o tabuleiro para ninguém mais mexer
                    document.querySelector('.board').style.pointerEvents = 'none';
                    
                    // Remove a seleção visual
                    selected.classList.remove('selected');
                    return; // IMPORTANTE: O "return" impede o reload para a mensagem não sumir
                }

                // 2. Se não foi vitória, apenas recarrega para o próximo turno
                window.location.reload();
            }
        })
        .catch(error => {
            console.error('Erro na requisição:', error);
            messageDiv.textContent = 'Erro de conexão com o servidor.';
        });
    });
});