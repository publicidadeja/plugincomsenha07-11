document.addEventListener('DOMContentLoaded', function() {
    const grid = document.querySelector('.gma-materiais-grid'); // Seleciona o container do grid

    // Delegação de eventos:
    grid.addEventListener('click', function(event) {
        const target = event.target;
        if (target.matches('.gma-button-primary, .gma-button-secondary')) {
            const materialId = target.closest('.gma-material').dataset.materialId; // Obtém o ID do material
            const campanhaId = target.closest('.gma-material').dataset.campanhaId; // Obtém o ID da campanha
            registrarClique(campanhaId, materialId, target);
        }
    });

    function registrarClique(campanhaId, materialId, button) {
        fetch(gmaAjax.ajaxurl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=gma_atualizar_clique&campanha_id=${campanhaId}&material_id=${materialId}&nonce=${gmaAjax.nonce}` // Envia o ID da campanha e material
        })
        .then(response => response.json())
        .then(data => {
    if (data.success) {
        if (data.playSound) {
            gmaNotify.playNotification();
        }
        console.log('Clique registrado com sucesso!');
        button.text('Status: Registrado');
        button.prop('disabled', true);
    } else {
                console.error('Erro ao registrar clique:', data.error);
                // Você pode exibir uma mensagem de erro para o usuário
            }
        })
        .catch(error => {
            console.error('Erro na requisição:', error);
            // Você pode exibir uma mensagem de erro para o usuário
        });
    }
});

jQuery(document).ready(function($) {
    $('form').on('submit', function() {
        $(this).find('input[type="submit"]').prop('disabled', true);
    });
});