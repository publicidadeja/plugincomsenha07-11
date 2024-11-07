(function($) {
    'use strict';

    $(document).ready(function() {
        // Animação de entrada suave
        function fadeInElements() {
            $('.gma-material').each(function(i) {
                $(this).delay(i * 100).animate({'opacity': '1', 'transform': 'translateY(0)'}, 500);
            });
        }

        // Iniciar animações de entrada
        fadeInElements();

        // Lazy loading para imagens
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const image = entry.target;
                        image.src = image.dataset.src;
                        image.classList.remove('lazy');
                        imageObserver.unobserve(image);
                    }
                });
            });

            document.querySelectorAll('img.lazy').forEach(img => imageObserver.observe(img));
        }

        // Copiar texto para a área de transferência
        $('.gma-copy').on('click', function() {
            var copyText = $(this).data('copy');
            var tempInput = $("<input>");
            $("body").append(tempInput);
            tempInput.val(copyText).select();
            document.execCommand("copy");
            tempInput.remove();

            // Feedback visual
            $(this).text('Copiado!').addClass('copied');
            setTimeout(() => {
                $(this).text('Copiar').removeClass('copied');
            }, 2000);
        });

        // Expandir/colapsar conteúdo
        $('.gma-expand-button').on('click', function() {
            var content = $(this).prev('.gma-content-expandable');
            content.toggleClass('collapsed');
            $(this).text(content.hasClass('collapsed') ? 'Expandir' : 'Colapsar');
        });

        // Animação de hover para cards
        $('.gma-material').hover(
            function() {
                $(this).find('.gma-material-overlay').css('opacity', '1');
            },
            function() {
                $(this).find('.gma-material-overlay').css('opacity', '0');
            }
        );

        // Scroll suave para links internos
        $('a[href^="#"]').on('click', function(event) {
            var target = $(this.getAttribute('href'));
            if(target.length) {
                event.preventDefault();
                $('html, body').stop().animate({
                    scrollTop: target.offset().top - 100
                }, 1000);
            }
        });

        // Validação de formulário
        $('.gma-form').submit(function(e) {
            var $form = $(this);
            var $requiredInputs = $form.find('[required]');
            var valid = true;

            $requiredInputs.each(function() {
                if (!$(this).val()) {
                    valid = false;
                    $(this).addClass('error');
                } else {
                    $(this).removeClass('error');
                }
            });

            if (!valid) {
                e.preventDefault();
                alert('Por favor, preencha todos os campos obrigatórios.');
            }
        });

        // Abrir o lightbox ao clicar na imagem
        $('.gma-material-image').on('click', function(e) {
            e.preventDefault();
            var imageUrl = $(this).attr('src');
            $('#lightboxImage').attr('src', imageUrl);
            $('#imageLightbox').fadeIn('fast');
        });

        // Fechar o lightbox ao clicar no botão de fechar ou fora da imagem
        $('.close-lightbox, .lightbox').click(function() {
            $('#imageLightbox').fadeOut('fast');
        });

        // Função para salvar feedback e edição
        function salvarFeedbackEEdicao(materialId, feedbackArte, copyEditada) {
            $.ajax({
                url: gmaAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'gma_salvar_feedback_e_edicao',
                    nonce: gmaAjax.nonce,
                    material_id: materialId,
                    feedback_arte: feedbackArte,
                    copy_editada: copyEditada
                },
                success: function(response) {
                    if (response.success) {
                        alert('Feedback e edição salvos com sucesso!');
                    } else {
                        alert('Erro ao salvar feedback e edição.');
                    }
                },
                error: function() {
                    alert('Erro ao enviar requisição.');
                }
            });
        }

        // Evento de clique para salvar feedback e edição
        $('.gma-save-feedback').on('click', function() {
            var materialId = $(this).data('material-id');
            var feedbackArte = $('#feedback-arte-' + materialId).val();
            var copyEditada = $('#copy-editada-' + materialId).val();
            salvarFeedbackEEdicao(materialId, feedbackArte, copyEditada);
        });
    });

})(jQuery);