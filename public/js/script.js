let selected = null;

document.querySelectorAll('.square').forEach(square => {
    square.addEventListener('click', () => {

        // Caso 1: nenhuma peça selecionada
        if (!selected) {
            if (square.textContent.trim() === '') {
                return; // clicou em casa vazia, ignora
            }

            selected = square;
            square.classList.add('selected');
            return;
        }

        // Caso 2: clicou na mesma casa → desmarca
        if (square === selected) {
            selected.classList.remove('selected');
            selected = null;
            return;
        }

        // Caso 3: clicou em outra peça → troca seleção
        if (square.textContent.trim() !== '') {
            selected.classList.remove('selected');
            selected = square;
            square.classList.add('selected');
            return;
        }

        // Caso 4: clicou em casa vazia → mover
        console.log('VOU MOVER');

        fetch('/chess/move', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document
                    .querySelector('meta[name="csrf-token"]')
                    .content
            },
            body: JSON.stringify({
                from_row: selected.dataset.row,
                from_col: selected.dataset.col,
                to_row: square.dataset.row,
                to_col: square.dataset.col
            })
        }).then(() => {
            selected.classList.remove('selected');
            selected = null;
            window.location.reload();
        });

    });
});
