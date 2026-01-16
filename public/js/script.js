let selected = null;

document.querySelectorAll('.square').forEach(square => {
    square.addEventListener('click', () => {

        // Caso 1: Nenhuma peça selecionada
        if (!selected) {
            if (square.textContent.trim() === '') return; // Casa vazia

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

        // Caso 3: Clicou em outra peça da MESMA cor → troca seleção
        // Se já temos uma selecionada e clicamos em OUTRA casa (vazia ou com peça inimiga)
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
            const data = await response.json(); // Lemos o JSON enviado pelo Controller

            if (!response.ok) {
                // Se o Laravel enviou erro (400), mostramos a mensagem que você criou no ChessGame
                alert(data.message || data.error);
                
                // Opcional: Desmarcar a peça em caso de erro
                selected.classList.remove('selected');
                selected = null;
            } else {
                // Se deu certo, recarrega para mostrar a nova posição
                window.location.reload();
            }
        })
        .catch(error => {
            console.error('Erro na requisição:', error);
            alert('Erro de conexão com o servidor.');
        });
    });
});